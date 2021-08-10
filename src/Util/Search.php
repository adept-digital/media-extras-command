<?php

namespace AdeptDigital\MediaCommands\Util;

use WP_Query;

class Search
{
    private const EXCLUDE_POST_TYPES = ['nav_menu_item', 'oembed_cache', 'user_request'];

    private static $postTypes;

    public static function isStringInPosts(string ...$values): bool
    {
        global $wpdb;

        if (count($values) < 1) {
            return false;
        }

        $search = [];
        foreach ($values as $value) {
            $search[] = Mysql::buildComparison('post_content', "%{$value}%", 'LIKE');
        }
        $search = implode(' OR ', $search);

        $postTypes = implode(',', array_map([Mysql::class, 'escapeValue'], self::getPostTypes()));
        $query = [
            'SELECT COUNT(*)',
            "FROM {$wpdb->posts}",
            "WHERE ({$search}) AND `post_type` IN ({$postTypes})",
            'LIMIT 1'
        ];
        $query = implode("\n", $query);
        return (bool)$wpdb->get_var($query);
    }

    private static function getPostTypes(): array
    {
        if (!isset(self::$postTypes)) {
            self::$postTypes = array_filter(get_post_types(), function (string $postType) {
                return (
                    !in_array($postType, self::EXCLUDE_POST_TYPES) &&
                    post_type_supports($postType, 'editor')
                );
            });
        }
        return self::$postTypes;
    }
}