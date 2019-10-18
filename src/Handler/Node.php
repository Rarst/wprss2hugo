<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Handler;

use Rarst\Hugo\wprss2hugo\Filesystem\Write;

/**
 * Base class for XML node handlers.
 */
abstract class Node implements Handler
{
    /** @var Write */
    protected $store;

    /**
     * Set up with instance of storage.
     */
    public function __construct(Write $store)
    {
        $this->store = $store;
    }

    /**
     * Handle XML node element.
     */
    abstract public function handle(\SimpleXMLElement $node): void;
}
