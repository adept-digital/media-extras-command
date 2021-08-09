<?php

namespace AdeptDigital\MediaCommands\Util;

class MimeTypes
{
    private static $groupedMimeTypes = null;

    public static function getGroups(): array
    {
        return array_keys(wp_get_ext_types());
    }

    public static function getTypes(): array
    {
        return array_values(wp_get_mime_types());
    }

    public static function getGroupedTypes(): array
    {
        if (self::$groupedMimeTypes !== null) {
            return self::$groupedMimeTypes;
        }

        $extensionTypes = wp_get_ext_types();
        $mimeTypes = wp_get_mime_types();

        self::$groupedMimeTypes = [];
        foreach ($extensionTypes as $group => $extensions) {
            foreach ($mimeTypes as $extMatch => $mimeType) {
                foreach ($extensions as $extension) {
                    if (preg_match("/^({$extMatch})$/i", $extension)) {
                        self::$groupedMimeTypes[$group][] = $mimeType;
                        unset($mimeTypes[$extMatch]);
                        break;
                    }
                }
            }
        }

        return self::$groupedMimeTypes;
    }

    public static function getTypesByGroup(string ...$group): array
    {
        $groupedMimeTypes = self::getGroupedTypes();
        $mimeTypes = [];
        foreach ($group as $type) {
            if (isset($groupedMimeTypes[$type])) {
                $mimeTypes = array_merge($mimeTypes, $groupedMimeTypes[$type]);
            }
        }
        return $mimeTypes;
    }

    public static function getGroupByType(string $type): ?string
    {
        $groupedMimeTypes = self::getGroupedTypes();
        foreach ($groupedMimeTypes as $group => $types) {
            if (in_array($type, $types)) {
                return $group;
            }
        }
        return null;
    }
}