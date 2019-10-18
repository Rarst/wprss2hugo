<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo;

use Prewk\XmlStringStreamer;
use Prewk\XmlStringStreamer\Parser\StringWalker;
use Prewk\XmlStringStreamer\Stream\File;

/**
 * Incremental wrapper for the XML export file.
 */
class Export implements \Iterator
{
    /** @var XmlStringStreamer */
    private $streamer;

    /** @var string */
    private $currentNode;

    /** @var int */
    private $size;

    /** @var int */
    private $bytes = 0;

    /**
     * Set up for a given export file path.
     */
    public function __construct(string $file)
    {
        $this->size     = filesize($file);
        $stream         = new File($file, 16384, function (string $chunk, int $readBytes) {
            $this->bytes = $readBytes;
        });
        $parser         = new StringWalker([
            'captureDepth' => 3,
            'expectGT'     => true,
        ]);
        $this->streamer = new XmlStringStreamer($parser, $stream);

        $this->next();
    }

    /**
     * File read progress in percent.
     */
    public function progress(): int
    {
        return (int)round($this->bytes / $this->size * 100);
    }

    /**
     * Current XML node.
     */
    public function current()
    {
        return $this->transform($this->currentNode);
    }

    /**
     * Convert XML string to an element object.
     */
    private function transform(string $xml): \SimpleXMLElement
    {
        return simplexml_load_string($this->removeNamespaces($xml));
    }

    /**
     * Remove namespaces from partial XML string for simplicity of processing.
     */
    private function removeNamespaces(string $xml): string
    {
        $xml = strtr($xml, [
            '<content:encoded>'  => '<content>',
            '</content:encoded>' => '</content>',
            '<excerpt:encoded>'  => '<excerpt>',
            '</excerpt:encoded>' => '</excerpt>',
        ]);

        $xml = preg_replace('~<([/]?)[[:alpha:]_]+?:([[:alpha:]_]+?)([/]?)>~', '<$1$2$3>', $xml);

        return $xml;
    }

    public function next()
    {
        $this->currentNode = (string)$this->streamer->getNode();
    }

    /**
     * Iterator key (not applicable).
     */
    public function key()
    {
        return null;
    }

    /**
     * If got valid node iteration.
     */
    public function valid()
    {
        return '' !== $this->currentNode;
    }

    public function rewind()
    {
    }
}
