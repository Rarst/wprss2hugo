<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

/**
 * Pass-through serialized for HTML.
 */
class Html implements Content
{
    /**
     * Return HTML input as-is.
     */
    public function serialize(string $input): string
    {
        return $input;
    }

    /**
     * HTML file type.
     */
    public function type(): string
    {
        return 'html';
    }
}
