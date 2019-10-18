<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Handler;

use Rarst\Hugo\wprss2hugo\Filesystem\Write;

/**
 * Handler for native and custom taxonomy terms.
 */
class Term extends Node
{
    /** @var Config */
    private $config;

    /**
     * Set up with instance of config to add custom taxonomies as necessary.
     */
    public function __construct(Write $store, Config $config)
    {
        parent::__construct($store);
        $this->config = $config;
    }

    /**
     * Handle a term XML element.
     */
    public function handle(\SimpleXMLElement $node): void
    {
        switch ($node->getName()) {
            case 'category':
                $this->category($node);
                break;

            case 'tag':
                $this->tag($node);
                break;

            case 'term':
                $this->term($node);
                break;
        }
    }

    /**
     * Handle a `category` XML node.
     */
    private function category(\SimpleXMLElement $node): void
    {
        $this->store->content(
            "content/categories/{$node->category_nicename}/_index",
            [
                'title' => (string)$node->cat_name,
                'slug'  => (string)$node->category_nicename,
            ],
            (string)$node->category_description,
            );
    }

    /**
     * Handle a `tag` XML node.
     */
    private function tag(\SimpleXMLElement $node): void
    {
        $this->store->content(
            "content/tags/{$node->tag_slug}/_index",
            [
                'title' => (string)$node->tag_name,
                'slug'  => (string)$node->tag_slug,
            ],
            (string)$node->tag_description
        );
    }

    /**
     * Handle a `term` XML node.
     */
    private function term(\SimpleXMLElement $node): void
    {
        $taxonomy = (string)$node->term_taxonomy;

        if ('nav_menu' === $taxonomy) {
            return;
        }

        $this->config->addTaxonomy($taxonomy, $taxonomy);

        $this->store->content(
            "content/{$taxonomy}/{$node->term_slug}/_index",
            [
                'title' => (string)$node->term_name,
                'slug'  => (string)$node->term_slug,
            ],
            (string)$node->term_description
        );
    }
}
