<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

/**
 * Interface for a content serializer, that accepts text and converts to a specific type.
 */
interface Content extends Type
{
    /**
     * Accept text input and convert to necessary type.
     */
    public function serialize(string $input): string;
}
