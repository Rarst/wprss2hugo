<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

/**
 * Interface for a serializer type.
 */
interface Type
{
    /**
     * Output type, used as file extension.
     */
    public function type(): string;
}
