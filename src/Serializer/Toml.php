<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

use Yosymfony\Toml\TomlBuilder;

/**
 * TOML serializer.
 */
class Toml implements Data
{
    /**
     * Process an array input into a TOML string.
     */
    public function serialize(array $input): string
    {
        $builder = new TomlBuilder();

        $tables = [];

        // TOML doesn't support root data array so we add dummy `data` key.
        if (is_numeric(array_key_first($input))) {

            foreach ($input as $item) {

                $builder->addArrayOfTable('data');

                foreach ($item as $key => $value) {
                    $builder->addValue($key, $value);
                }
            }

            return $builder->getTomlString();
        }

        foreach ($input as $key => $value) {

            // Associative arrays map to TOML tables and shouldn't be mixed between regular values.
            if (is_array($value) && !is_numeric(array_key_first($value))) {
                $tables[$key] = $value;
                continue;
            }

            $builder->addValue((string)$key, $value);
        }

        foreach ($tables as $name => $items) {

            $builder->addTable($name);

            foreach ($items as $itemKey => $itemValue) {
                $builder->addValue((string)$itemKey, $itemValue);
            }

        }

        return $builder->getTomlString();
    }

    /**
     * TOML file type.
     */
    public function type(): string
    {
        return 'toml';
    }
}
