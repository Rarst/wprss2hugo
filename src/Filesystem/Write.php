<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Filesystem;

/**
 * Interface to write content and data to a destination paths.
 */
interface Write
{
    /**
     * Write front matter and content to a destination path.
     */
    public function content(string $path, array $frontMatter = [], string $content = ''): void;

    /**
     * Write data to a destination path.
     */
    public function data(string $path, array $data = []): void;
}
