<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

use League\HTMLToMarkdown\HtmlConverter;

/**
 * Markdown serializer.
 */
class Markdown implements Content
{
    /**
     * Convert HTML string to Markdown.
     */
    public function serialize(string $input): string
    {
        $converter = new HtmlConverter(['header_style' => 'atx']);

        return $converter->convert($input);
    }

    /**
     * Markdown file type.
     */
    public function type(): string
    {
        return 'md';
    }
}
