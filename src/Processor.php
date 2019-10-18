<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo;

use Rarst\Hugo\wprss2hugo\Handler\Handler;

/**
 * Main process class, what routes XML nodes to matching handlers.
 */
class Processor implements Store
{
    /** @var Handler[] */
    private $handlers = [];

    /** @var array */
    private $counts = [];

    /**
     * Add a Handler instance for a given XML node type.
     */
    public function addHandler(string $type, Handler $handler): void
    {
        $this->handlers[$type] = $handler;
    }

    /**
     * Process a XML node instance.
     */
    public function push(\SimpleXMLElement $node): void
    {
        $type = $node->getName();

        if (isset($this->handlers[$type])) {
            $this->handlers[$type]->handle($node);

            if (!isset($this->counts[$type])) {
                $this->counts[$type] = 0;
            }

            $this->counts[$type]++;
        }
    }

    public function store(): void
    {
        foreach ($this->handlers as $handler) {
            if ($handler instanceof Store) {
                $handler->store();
            }
        }
    }

    /**
     * Retrieve counts of processed XML node instances, grouped by type.
     */
    public function getCounts(): array
    {
        return array_filter($this->counts);
    }
}
