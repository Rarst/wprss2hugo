<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo;

use Pimple\Container;
use Silly\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$container = new Container();
$container->register(new Services());
$psrContainer = new \Pimple\Psr11\Container($container);

$app->useContainer($psrContainer);
$app->command('import file [--content-type=] [--front-matter-type=] [--data-type=]', 'command.import')
    ->descriptions('Import a WordPress export file.', [
        'file'                => 'Path to a WordPress export XML file.',
        '--content-type'      => 'html|md',
        '--front-matter-type' => 'yaml|toml|json',
        '--data-type'         => 'yaml|toml|json',
    ])
    ->defaults([
        'content-type'      => 'html',
        'front-matter-type' => 'yaml',
        'data-type'         => 'yaml',
    ]);
$app->setDefaultCommand('import', true);

$app->run();