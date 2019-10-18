<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Handler;

/**
 * Interface to handle an XML node.
 */
interface Handler
{
    /**
     * Handle XML node in whatever way necessary.
     */
    public function handle(\SimpleXMLElement $node): void;
}
