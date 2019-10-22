<?php
declare(strict_types=1);

namespace Rarst\Hugo\wprss2hugo\Handler;

/**
 * Handler for all post types.
 */
class Post extends Node
{
    /**
     * Handle `item` XML node with a post entry.
     */
    public function handle(\SimpleXMLElement $node): void
    {
        $type = (string)$node->post_type;

        if ('nav_menu_item' === $type) {
            return;
        }

        $slug = ((string)$node->post_name) ?: (string)$node->title;

        $this->store->content("content/{$type}/{$slug}", $this->frontMatter($node), (string)$node->content);

        if (0 !== count($node->comment)) {
            $this->store->data("data/comments/{$node->post_id}", $this->comments($node->comment));
        }
    }

    /**
     * Build the front matter data from an XML node.
     */
    private function frontMatter(\SimpleXMLElement $node): array
    {
        $url = (string)$node->link;

        if ($url) {
            $url = parse_url($url, PHP_URL_PATH);
        }

        if ('/' === $url) {
            $url = false;
        }

        return array_merge(
            [
                'title'         => (string)$node->title,
                'publishDate'   => $this->date((string)$node->post_date, (string)$node->post_date_gmt),
                'summary'       => (string)$node->excerpt,
                'authors'       => [(string)$node->creator],
                'draft'         => !in_array((string)$node->status, ['publish', 'future', 'inherit'])
                    || !empty((string)$node->post_password),
                'attachmenturl' => (string)$node->attachment_url,
                'id'            => (int)$node->post_id,
                'parentid'      => (int)$node->post_parent,
                'url'           => $url,
            ],
            $this->terms($node->category),
            ['meta' => $this->meta($node->postmeta)],
        );
    }

    /**
     * Extract terms from `category` XML elements.
     */
    private function terms(\SimpleXMLElement $terms): array
    {
        $result = [];

        foreach ($terms as $term) {
            $taxonomy = (string)$term['domain'];
            $term     = (string)$term['nicename'];

            $taxonomy = strtr($taxonomy, [
                'category'    => 'categories',
                'post_tag'    => 'tags',
                'post_format' => 'formats',
            ]);

            $term = str_replace('post-format-', '', $term);

            $result[$taxonomy][] = $term;
        }

        return $result;
    }

    /**
     * Extract post meta from `postmeta` XML elements.
     */
    private function meta(\SimpleXMLElement $meta): array
    {
        $result       = [];
        $multipleMeta = [];

        foreach ($meta as $entry) {
            $key   = (string)$entry->meta_key;
            $value = $this->unserialize((string)$entry->meta_value);

            if (isset($result[$key])) {

                if (isset($multipleMeta[$key])) {
                    $result[$key][] = $value;
                } else {
                    $result[$key]       = [$result[$key], $value];
                    $multipleMeta[$key] = true;
                }
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Extract comments from `comment` XML elements.
     */
    private function comments(\SimpleXMLElement $commentsElement): array
    {
        $comments = [];

        foreach ($commentsElement as $comment) {
            $comments[] = array_filter([
                'id'       => (int)$comment->comment_id,
                'author'   => (string)$comment->comment_author,
                'email'    => (string)$comment->comment_author_email,
                'url'      => (string)$comment->comment_author_url,
                'ip'       => (string)$comment->comment_author_IP,
                'date'     => $this->date((string)$comment->comment_date, (string)$comment->comment_date_gmt),
                'content'  => (string)$comment->comment_content,
                'approved' => (bool)$comment->comment_approved,
                'type'     => (string)$comment->comment_type,
                'parentId' => (int)$comment->comment_parent,

            ]);
        }

        return $comments;
    }

    /**
     * Meta value might be PHP-serialized, if so we need to unpack it.
     *
     * @return string|array
     */
    private function unserialize(string $value)
    {
        return @unserialize($value, ['allowed_classes' => false]) ?: $value;
    }

    /**
     * Figure out a date with correct time zone offset, from a difference between UTC and local times.
     */
    private function date(string $local, string $utc): ?string
    {
        $utcTimeZone   = new \DateTimeZone('UTC');
        $localDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $local, $utcTimeZone);
        $utcDateTime   = \DateTime::createFromFormat('Y-m-d H:i:s', $utc, $utcTimeZone);

        if (false === $localDateTime) {
            if (false === $utcDateTime) {
                return null;
            }

            return $utcDateTime->format(DATE_RFC3339);
        }

        if (false === $utcDateTime) {
            return $localDateTime->format('Y-m-d\TH:i:s');
        }

        $difference = $localDateTime->getTimestamp() - $utcDateTime->getTimestamp();
        $timezone   = new \DateTimeZone($this->timezoneString($difference));

        return $utcDateTime->setTimezone($timezone)->format(DATE_RFC3339);
    }

    /**
     * Convert a difference in seconds into ±NN:NN offset.
     */
    private function timezoneString(int $seconds): string
    {
        $offset  = $seconds / 3600;
        $hours   = (int)$offset;
        $minutes = ($offset - $hours);

        $sign     = ($offset < 0) ? '-' : '+';
        $abs_hour = abs($hours);
        $abs_mins = abs($minutes * 60);

        return sprintf('%s%02d:%02d', $sign, $abs_hour, $abs_mins);
    }
}
