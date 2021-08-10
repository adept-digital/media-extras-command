<?php

namespace AdeptDigital\MediaCommands\Util;

use WP_Query;

class Search
{
    private const EXCLUDE_POST_TYPES = ['nav_menu_item', 'oembed_cache'];

    private static $postTypes;

    public static function isStringInPosts(string $url): bool
    {
        $query = new WP_Query([
            's' => $url,
            'sentence' => true,
            'post_type' => self::getPostTypes(),
            'fields' => 'ids',
            'posts_per_page' => 1,
        ]);
        return $query->have_posts();
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