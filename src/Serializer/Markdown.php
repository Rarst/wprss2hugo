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

        // Override HTML comments handling to preserve 'more' tags.
        $environment = $converter->getEnvironment();
        $environment->addConverter(new CommentConverter());

        $markdown = $converter->convert($input);
        // Ends up with prepended slash for whatever reason...
        $markdown = str_replace('\<!--more-->', '<!--more-->', $markdown);

        return $markdown;
    }

    /**
     * Markdown file type.
     */
    public function type(): string
    {
        return 'md';
    }
}
