<?php

namespace AdeptDigital\MediaCommands;

use AdeptDigital\MediaCommands\Query\MediaQuery;
use Generator;
use WP_CLI\Formatter as Output;

class MediaSearchCommand
{
    private const DEFAULT_FIELDS = [
        'id',
        'file_name',
        'post_date',
        'post_status',
    ];

    /**
     * Search media.
     *
     * ## OPTIONS
     *
     * [<keyword>...]
     * : Search by keyword
     *
     * [--media_type=<type>]
     * : Media type
     * ---
     * options:
     *   - image
     *   - audio
     *   - video
     *   - document
     *   - spreadsheet
     *   - interactive
     *   - text
     *   - archive
     *   - code
     * ---
     *
     * [--fields=<fields>]
     * : Limit output to specific fields.
     *
     * [--attached]
     * : Attached media
     *
     * [--unattached]
     * : Unattached media
     *
     * [--used]
     * : Media in use (experimental)
     *
     * [--unused]
     * : Media not in use (experimental)
     *
     * [--file_exists]
     * : File exists
     *
     * [--file_missing]
     * : File is missing
     *
     * [--post_parent=<post_id>]
     * : Unattached media
     *
     * [--media_width=<width>]
     * : Exact width
     *
     * [--media_width_min=<width>]
     * : Minimum width
     *
     * [--media_width_max=<width>]
     * : Maximum width
     *
     * [--media_height=<height>]
     * : Exact height
     *
     * [--media_height_min=<height>]
     * : Minimum height
     *
     * [--media_height_max=<height>]
     * : Maximum height
     *
     * [--file_size=<size>]
     * : Exact file size
     *
     * [--file_size_min=<size>]
     * : Minimum file size
     *
     * [--file_size_max=<size>]
     * : Maximum file size
     *
     * [--post_date=<date>]
     * : Post date
     *
     * [--post_date_min=<date>]
     * : Minimum post date
     *
     * [--post_date_max=<date>]
     * : Maximum post date
     *
     * [--format=<format>]
     * : Output format
     * ---
     * default: table
     * options:
     *   - table
     *   - json
     *   - csv
     *   - yaml
     *   - ids
     *   - count
     * ---
     *
     * [--order=<field-direction>]
     * : Sorting order
     *
     * [--limit=<number>]
     * : Limit results
     *
     * ## AVAILABLE FIELDS
     *
     * These fields will be displayed by default for each media:
     *
     * * ID
     * * file_name
     * * post_date
     * * post_status
     *
     * These fields are optionally available:
     *
     * * file_url
     * * file_path
     * * file_size
     * * media_type
     * * media_size
     * * media_width
     * * media_height
     * * post_author
     * * post_parent
     * * post_modified
     * * post_mime_type
     * * is_in_use
     * * is_attached
     * * is_file_exists
     */
    public function __invoke(array $arguments = [], array $options = [])
    {
        $query = $this->buildQuery($arguments, $options);
        $results = $this->formatResults($query->getResults());
        $fields = isset($options['fields']) ? explode(',', $options['fields']) : self::DEFAULT_FIELDS;
        $output = new Output($options, $fields);
        $output->display_items($results);
    }

    private function buildQuery(array $keywords = [], array $options = []): MediaQuery
    {
        $query = new MediaQuery();

        if (!empty($keywords)) {
            $query->setKeyword(...$keywords);
        }

        if (isset($options['file_size'])) {
            $query->setFileSize((int)$options['file_size']);
        }

        if (isset($options['file_size_min'])) {
            $query->setFileSizeMin((int)$options['file_size_min']);
        }

        if (isset($options['file_size_max'])) {
            $query->setFileSizeMax((int)$options['file_size_max']);
        }

        if (isset($options['media_type'])) {
            $query->setMediaType(...explode(',', $options['media_type']));
        }

        if (isset($options['media_width'])) {
            $query->setMediaWidth((int)$options['media_width']);
        }

        if (isset($options['media_width_min'])) {
            $query->setMediaWidthMin((int)$options['media_width_min']);
        }

        if (isset($options['media_width_max'])) {
            $query->setMediaWidthMax((int)$options['media_width_max']);
        }

        if (isset($options['media_height'])) {
            $query->setMediaHeight((int)$options['media_height']);
        }

        if (isset($options['media_height_min'])) {
            $query->setMediaHeightMin((int)$options['media_height_min']);
        }

        if (isset($options['media_height_max'])) {
            $query->setMediaHeightMax((int)$options['media_height_max']);
        }

        if (isset($options['post_author'])) {
            $query->setPostAuthor(...explode(',', $options['post_author']));
        }

        if (isset($options['post_parent'])) {
            $query->setPostParent(...explode(',', $options['post_parent']));
        }

        if (!empty($options['attached'])) {
            $query->setIsAttached(true);
        } elseif (!empty($options['unattached'])) {
            $query->setIsAttached(false);
        }

        if (!empty($options['used'])) {
            $query->setIsInUse(true);
        } elseif (!empty($options['unused'])) {
            $query->setIsInUse(false);
        }

        if (!empty($options['file_exists'])) {
            $query->setIsFileExists(true);
        } elseif (!empty($options['file_missing'])) {
            $query->setIsFileExists(false);
        }

        if (isset($options['post_date'])) {
            $query->setPostDate($options['post_date']);
        }

        if (isset($options['post_date_min'])) {
            $query->setPostDateMin($options['post_date_min']);
        }

        if (isset($options['post_date_max'])) {
            $query->setPostDateMax($options['post_date_max']);
        }

        if (isset($options['post_modified'])) {
            $query->setPostModified($options['post_modified']);
        }

        if (isset($options['post_modified_min'])) {
            $query->setPostModifiedMin($options['post_modified_min']);
        }

        if (isset($options['post_modified_max'])) {
            $query->setPostModifiedMax($options['post_modified_max']);
        }

        if (isset($options['post_mime_type'])) {
            $query->setPostMimeType($options['post_mime_type']);
        }

        if (isset($options['order'])) {
            $orders = [];
            foreach (explode(',', $options['order']) as $order) {
                $order = explode('.', $order, 2);
                $orders[$order[0]] = $order[1] ?? 'DESC';
            }
            $query->setOrder($orders);
        }

        if (isset($options['limit'])) {
            $query->setLimit((int)$options['limit']);
        }

        return $query;
    }

    private function formatResults(Generator $results): Generator
    {
        foreach ($results as $result) {
            yield new MediaFormatter($result);
        }
    }
}