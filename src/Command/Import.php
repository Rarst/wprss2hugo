<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Command;

use Pimple\Container;
use Rarst\Hugo\wprss2hugo\Export;
use Rarst\Hugo\wprss2hugo\Processor;
use Rarst\Hugo\wprss2hugo\Serializer\Html;
use Rarst\Hugo\wprss2hugo\Serializer\Json;
use Rarst\Hugo\wprss2hugo\Serializer\Markdown;
use Rarst\Hugo\wprss2hugo\Serializer\Toml;
use Rarst\Hugo\wprss2hugo\Serializer\Yaml;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command callable for import.
 */
class Import
{
    /** @var Container */
    private $container;

    /**
     * Set up with dependency injection container instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Command callback with command line arguments.
     */
    public function __invoke(
        string $file,
        OutputInterface $output,
        string $contentType = 'html',
        string $frontMatterType = 'yaml',
        string $dataType = 'yaml'
    ) {
        $export      = new Export($file);
        $processor   = $this->processor($contentType, $frontMatterType, $dataType);
        $progressBar = new ProgressBar($output, 100);
        foreach ($export as $node) {
            $processor->push($node);
            $progressBar->setProgress($export->progress());
        }

        $progressBar->finish();
        $output->write(PHP_EOL . PHP_EOL);
        $processor->store();

        foreach ($processor->getCounts() as $type => $count) {
            $output->writeln("Processed '{$type}': {$count}");
        }
    }

    /**
     * Pass command line arguments to the container and retrieve configured Processor instance.
     */
    private function processor(string $content, string $frontMatter, string $data): Processor
    {
        $this->container['serializer.content.class']      = $this->serializerClass($content);
        $this->container['serializer.front-matter.class'] = $this->serializerClass($frontMatter);
        $this->container['serializer.data.class']         = $this->serializerClass($data);

        return $this->container['processor'];
    }

    /**
     * Retrieve serializer class name for a given file type.
     */
    private function serializerClass(string $type): string
    {
        switch ($type) {
            case 'yaml':
                return Yaml::class;
                break;
            case 'toml':
                return Toml::class;
                break;
            case 'json':
                return Json::class;
                break;
            case 'html':
                return Html::class;
                break;
            case 'md':
                return Markdown::class;
                break;
        }

        throw new \UnexpectedValueException("Unknown type '{$type}'.");
    }
}
