<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Handler;

/**
 * Handle author entries and store as taxonomy terms.
 */
class Author extends Node
{
    /**
     * Handle `author` XML node.
     */
    public function handle(\SimpleXMLElement $node): void
    {
        $this->store->content("content/authors/{$node->author_login}/_index", [
            'title'     => (string)$node->author_display_name,
            'name'      => (string)$node->author_display_name,
            'firstName' => (string)$node->author_first_name,
            'lastName'  => (string)$node->author_last_name,
            'email'     => (string)$node->author_email,
            'slug'      => (string)$node->author_login,
        ]);
    }
}
