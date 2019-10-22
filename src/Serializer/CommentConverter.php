<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

use League\HTMLToMarkdown\ElementInterface;

/**
 * Extend comment converter to preserve 'more' tags.
 */
class CommentConverter extends \League\HTMLToMarkdown\Converter\CommentConverter
{
    /**
     * Discard HTML comments, except 'more' tag.
     */
    public function convert(ElementInterface $element)
    {
        if ('more' === $element->getValue()) {
            return '<!--more-->';
        }

        return '';
    }
}
