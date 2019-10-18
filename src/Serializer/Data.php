<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

/**
 * Interface for a data serializer, that accepts data input and converts to a specific type.
 */
interface Data extends Type
{
    /**
     * Accept data input and convert to a string of necessary type.
     */
    public function serialize(array $input): string;
}
