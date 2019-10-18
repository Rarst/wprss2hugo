<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Serializer;

use \Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * YAML serializer.
 */
class Yaml implements Data
{
    /**
     * Serialize data input into a YAML string.
     */
    public function serialize(array $input): string
    {
        return SymfonyYaml::dump($input, 2, 4, SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }

    /**
     * YAML file type.
     */
    public function type(): string
    {
        return 'yaml';
    }
}
