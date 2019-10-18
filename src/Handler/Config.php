<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Handler;

use Rarst\Hugo\wprss2hugo\Filesystem\Write;
use Rarst\Hugo\wprss2hugo\Store;

/**
 * Site-side configuration, with defaults, adding things, and storing result.
 */
class Config implements Handler, Store
{
    /** @var array{params: array<string,string>} */
    private $config = [
        'title'      => '',
        'baseURL'    => '',
        'params'     => [
            'description' => '',
        ],
        'permalinks' => [ // WP term permalinks are typically singular.
            'categories' => '/category/:slug/',
            'tags'       => '/tag/:slug/',
            'formats'    => '/format/:slug/',
            'authors'    => '/author/:slug/',
        ],
        'taxonomies' => [
            'category' => 'categories',
            'tag'      => 'tags',
            'format'   => 'formats',
            'author'   => 'authors',
        ],
    ];

    /** @var Write */
    private $store;

    /**
     * Set up with writable store.
     */
    public function __construct(Write $store)
    {
        $this->store = $store;
    }

    /**
     * Handle configuration-related XML nodes.
     */
    public function handle(\SimpleXMLElement $node): void
    {
        switch ($node->getName()) {
            case 'title':
                $this->config['title'] = (string)$node;
                break;

            case 'description':
                $this->config['params']['description'] = (string)$node;
                break;

            case 'link':
                $this->config['baseURL'] = (string)$node;
                break;
        }
    }

    /**
     * Add a taxonomy to the configuration.
     */
    public function addTaxonomy(string $singular, string $plural): void
    {
        if (!isset($this->config['taxonomies'][$singular])) {
            $this->config['taxonomies'][$singular] = $plural;
        }
    }

    public function store(): void
    {
        $this->store->data('config', $this->config);
    }
}
