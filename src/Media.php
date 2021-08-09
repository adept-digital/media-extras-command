<?php

namespace AdeptDigital\MediaCommands;

use AdeptDigital\MediaCommands\Util\MimeTypes;
use DateTimeInterface;
use WP_Post;
use WP_User;

class Media
{
    /** @var int */
    private $id;

    /** @var WP_Post */
    private $post;

    /** @var array */
    private $meta;

    /** @var string */
    private $fileName;

    /** @var string */
    private $fileUrl;

    /** @var string */
    private $filePath;

    /** @var int */
    private $fileSize;

    /** @var string */
    private $postStatus;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        if (!isset($this->fileName)) {
            $this->fileName = get_post_meta($this->id, '_wp_attached_file', true);
        }
        return $this->fileName;
    }

    public function getFilePath(): string
    {
        if (!isset($this->filePath)) {
            $this->filePath = get_attached_file($this->id);
        }
        return $this->filePath;
    }

    public function getFileUrl(): string
    {
        if (!isset($this->fileUrl)) {
            $this->fileUrl = wp_get_attachment_url($this->id);
        }
        return $this->fileUrl;
    }

    public function getFileSize(): int
    {
        if (!isset($this->fileSize)) {
            $this->fileSize = $this->isFileExists() ? filesize($this->getFilePath()) : 0;
        }
        return $this->fileSize;
    }

    public function getMediaType(): string
    {
        return MimeTypes::getGroupByType($this->getPost()->post_mime_type);
    }

    public function getMediaSize(): ?string
    {
        $meta = $this->getMeta();
        if (!isset($meta['width'], $meta['height'])) {
            return null;
        }
        return "{$meta['width']}x{$meta['height']}";
    }

    public function getMediaWidth(): ?int
    {
        return $this->getMeta()['width'] ?? null;
    }

    public function getMediaHeight(): ?int
    {
        return $this->getMeta()['height'] ?? null;
    }

    public function getPostDate(): DateTimeInterface
    {
        return get_post_datetime($this->getPost());
    }

    public function getPostModified(): DateTimeInterface
    {
        return get_post_datetime($this->getPost(), 'modified');
    }

    public function getPostStatus(): string
    {
        if (!isset($this->postStatus)) {
            $this->postStatus = get_post_status($this->getPost());
        }
        return $this->postStatus;
    }

    public function getPostAuthor(): ?WP_User
    {
        if ($this->getPost()->post_author) {
            $user = new WP_User($this->getPost()->post_author);
            return $user->exists() ? $user : null;
        }
        return null;
    }

    public function getPostMimeType(): string
    {
        return $this->getPost()->post_mime_type;
    }

    public function getPostParent(): ?WP_Post
    {
        return get_post($this->getPost()->post_parent);
    }

    public function isFileExists(): bool
    {
        return file_exists($this->getFilePath());
    }

    private function getPost(): WP_Post
    {
        if (!isset($this->post)) {
            $this->post = get_post($this->id);
        }
        return $this->post;
    }

    private function getMeta(): array
    {
        if (!isset($this->meta)) {
            $this->meta = wp_get_attachment_metadata($this->id);
        }
        return $this->meta;
    }
}