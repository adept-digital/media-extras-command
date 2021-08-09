<?php

namespace AdeptDigital\MediaCommands;

use DateTimeInterface;
use OutOfRangeException;
use WP_Post;
use WP_User;

class MediaFormatter
{
    private const METHOD_MAP = [
        'id' => 'getId',
        'file_name' => 'getFileName',
        'file_path' => 'getFilePath',
        'file_url' => 'getFileUrl',
        'file_size' => 'getFileSize',
        'media_type' => 'getMediaType',
        'media_size' => 'getMediaSize',
        'media_width' => 'getMediaWidth',
        'media_height' => 'getMediaHeight',
        'post_date' => 'getPostDate',
        'post_modified' => 'getPostModified',
        'post_status' => 'getPostStatus',
        'post_author' => 'getPostAuthor',
        'post_mime_type' => 'getPostMimeType',
        'post_parent' => 'getPostParent',
    ];

    private const FORMAT_MAP = [
        'post_author' => 'formatUser',
        'post_parent' => 'formatPost',
        'post_date' => 'formatDate',
        'post_modified' => 'formatDate',
        'file_size' => 'formatFileSize',
    ];

    /** @var Media */
    private $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    private function formatUser(?WP_User $user): ?string
    {
        return $user ? $user->display_name : null;
    }

    private function formatPost(?WP_Post $post): ?string
    {
        return $post ? $post->post_name : null;
    }

    private function formatDate(?DateTimeInterface $date): ?string
    {
        return $date ? $date->format('Y-m-d H:i:s') : null;
    }

    private function formatFileSize(int $size): ?string
    {
        return size_format($size);
    }

    public function __isset($name)
    {
        return isset(self::METHOD_MAP[$name]);
    }

    public function __get($name)
    {
        if (!$this->__isset($name)) {
            throw new OutOfRangeException("Invalid offset `{$name}`.");
        }

        $value = $this->media->{self::METHOD_MAP[$name]}();
        if (isset(self::FORMAT_MAP[$name])) {
            $value = $this->{self::FORMAT_MAP[$name]}($value);
        }
        return $value;
    }

    public function __toString(): string
    {
        return (string)$this->media->getId();
    }
}