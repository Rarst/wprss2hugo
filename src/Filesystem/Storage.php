<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Filesystem;

use Rarst\Hugo\wprss2hugo\Serializer\Content;
use Rarst\Hugo\wprss2hugo\Serializer\Data;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Main storage implementation that writes files to filesystem.
 */
class Storage implements Write
{
    /** @var string */
    private $basePath;

    /** @var Filesystem */
    private $filesystem;

    /** @var Data */
    private $frontMatter;

    /** @var Content */
    private $content;

    /** @var Data */
    private $data;

    /**
     * Set up with path and dependencies.
     */
    public function __construct(
        string $basePath,
        Filesystem $filesystem,
        Content $content,
        Data $frontMatter,
        Data $data
    ) {
        $this->basePath    = $basePath;
        $this->filesystem  = $filesystem;
        $this->frontMatter = $frontMatter;
        $this->content     = $content;
        $this->data        = $data;
    }

    /**
     * Write a content file to a path.
     */
    public function content(string $path, array $frontMatter = [], string $content = ''): void
    {
        switch ($this->frontMatter->type()) {

            case 'toml':
                $prefix = '+++' . PHP_EOL;
                $suffix = '+++' . PHP_EOL;
                break;

            case 'yaml':
                $prefix = '---' . PHP_EOL;
                $suffix = '---' . PHP_EOL;
                break;

            default:
                $prefix = '';
                $suffix = PHP_EOL;
        }

        if (false === stripos($content, '<p>')) { // Lacks wpautop() paragraphs.
            $content = nl2br($content);
        }

        $this->save(
            "{$path}.{$this->content->type()}",
            $prefix
            . $this->frontMatter->serialize(array_filter($frontMatter))
            . $suffix
            . $this->content->serialize($content)
        );
    }

    /**
     * Write a data file to a path.
     */
    public function data(string $path, array $data = []): void
    {
        $this->save(
            "{$path}.{$this->data->type()}",
            $this->data->serialize($data)
        );
    }

    /**
     * Convert a content file (e.g. `page-name.md`), if it exists, into an index one (e.g. `page-name/index.md`).
     */
    private function makeIndex(string $path): void
    {
        $filePath = "{$path}.{$this->content->type()}";

        if ($this->exists($filePath)) {
            $this->move($filePath, "{$path}/_index.md");
        }
    }

    /**
     * Write a string to a path.
     */
    private function save(string $path, string $content): void
    {
        $this->filesystem->dumpFile($this->basePath . '/' . $path, $content);
    }

    /**
     * Check if a file exists ata a given path.
     */
    private function exists(string $path): bool
    {
        return $this->filesystem->exists($this->basePath . '/' . $path);
    }

    /**
     * Move a file from a path to a destination path.
     */
    private function move(string $from, string $to): void
    {
        try {
            // Doing this in one move with `rename()` errors for whatever reason.
            $this->filesystem->copy(
                $this->basePath . '/' . $from,
                $this->basePath . '/' . $to
            );
            $this->filesystem->remove($this->basePath . '/' . $from);
        } catch (\Exception $e) {
            // TODO this might happen if files try to get on top of each other. Error in console about it?
        }
    }
}
