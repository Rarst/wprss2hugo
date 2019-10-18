<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Rarst\Hugo\wprss2hugo\Command\Import;
use Rarst\Hugo\wprss2hugo\Filesystem\Storage;
use Rarst\Hugo\wprss2hugo\Filesystem\Write;
use Rarst\Hugo\wprss2hugo\Handler\Author;
use Rarst\Hugo\wprss2hugo\Handler\Config;
use Rarst\Hugo\wprss2hugo\Handler\Handler;
use Rarst\Hugo\wprss2hugo\Handler\Post;
use Rarst\Hugo\wprss2hugo\Handler\Term;
use Rarst\Hugo\wprss2hugo\Serializer\Content;
use Rarst\Hugo\wprss2hugo\Serializer\Html;
use Rarst\Hugo\wprss2hugo\Serializer\Data;
use Rarst\Hugo\wprss2hugo\Serializer\Yaml;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Default services definitions for a dependency injection container.
 *
 * @psalm-suppress MoreSpecificReturnType
 * @psalm-suppress LessSpecificReturnStatement
 */
class Services implements ServiceProviderInterface
{
    /**
     * Register services on a container.
     */
    public function register(Container $container): void
    {
        $container['serializer.content.class'] = Html::class;
        $container['serializer.content']       = static function () use ($container) : Content {
            return new $container['serializer.content.class'];
        };

        $container['serializer.front-matter.class'] = Yaml::class;
        $container['serializer.front-matter']       = static function () use ($container) : Data {
            return new $container['serializer.front-matter.class'];
        };

        $container['serializer.data.class'] = Yaml::class;
        $container['serializer.data']       = static function () use ($container) : Data {
            return new $container['serializer.data.class'];
        };

        $container['filesystem'] = static function (): Filesystem {
            return new Filesystem();
        };

        $container['storage'] = static function () use ($container) : Write {
            return new Storage(
                'output', $container['filesystem'], $container['serializer.content'],
                $container['serializer.front-matter'], $container['serializer.data']
            );
        };

        $container['handler.config'] = static function () use ($container) : Config {
            return new Config($container['storage']);
        };

        $container['handler.author'] = static function () use ($container) : Handler {
            return new Author($container['storage']);
        };

        $container['handler.term'] = static function () use ($container) : Handler {
            return new Term($container['storage'], $container['handler.config']);
        };

        $container['handler.post'] = static function () use ($container) : Handler {
            return new Post($container['storage']);
        };

        $container['processor'] = static function () use ($container) : Processor {
            $processor = new Processor();
            $config    = $container['handler.config'];
            $term      = $container['handler.term'];

            $processor->addHandler('title', $config);
            $processor->addHandler('description', $config);
            $processor->addHandler('link', $config);
            $processor->addHandler('author', $container['handler.author']);
            $processor->addHandler('category', $term);
            $processor->addHandler('tag', $term);
            $processor->addHandler('term', $term);
            $processor->addHandler('item', $container['handler.post']);

            return $processor;
        };

        $container['command.import'] = static function () use ($container) : Import {
            return new Import($container);
        };
    }
}
