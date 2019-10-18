<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

/**
 * JSON serializer.
 */
class Json implements Data
{
    /**
     * Encode array data as JSON.
     */
    public function serialize(array $input): string
    {
        return json_encode($input, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_THROW_ON_ERROR) . PHP_EOL;
    }

    /**
     * JSON file type.
     */
    public function type(): string
    {
        return 'json';
    }
}
