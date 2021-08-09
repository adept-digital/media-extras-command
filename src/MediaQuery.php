<?php

namespace AdeptDigital\MediaCommands;

use AdeptDigital\MediaCommands\Util\MimeTypes;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Traversable;

class MediaQuery
{
    private const DEFAULT_QUERY = [
        'post_type' => 'attachment',
        'fields' => 'ids',
    ];

    private const SORTING_FUNCTIONS = [
        'file_size' => 'sortFileSize',
        'media_width' => 'sortMediaWidth',
        'media_height' => 'sortMediaHeight',
    ];

    /** @var string[] */
    private $keyword = [];

    /** @var int|null */
    private $fileSize = null;

    /** @var int|null */
    private $fileSizeMin = null;

    /** @var int|null */
    private $fileSizeMax = null;

    /** @var string[] */
    private $mediaType = [];

    /** @var int|null */
    private $mediaWidth = null;

    /** @var int|null */
    private $mediaWidthMin = null;

    /** @var int|null */
    private $mediaWidthMax = null;

    /** @var int|null */
    private $mediaHeight = null;

    /** @var int|null */
    private $mediaHeightMin = null;

    /** @var int|null */
    private $mediaHeightMax = null;

    /** @var int[] */
    private $postAuthor = [];

    /** @var int[] */
    private $postParent = [];

    /** @var bool|null */
    private $postAttached = null;

    /** @var DateTimeInterface|null */
    private $postDate = null;

    /** @var DateTimeInterface|null */
    private $postDateMin = null;

    /** @var DateTimeInterface|null */
    private $postDateMax = null;

    /** @var DateTimeInterface|null */
    private $postModified = null;

    /** @var DateTimeInterface|null */
    private $postModifiedMin = null;

    /** @var DateTimeInterface|null */
    private $postModifiedMax = null;

    /** @var string[] */
    private $postMimeType = [];

    /** @var string[] */
    private $order = ['post_date' => 'DESC'];

    /** @var int|null */
    private $limit = null;

    public function setKeyword(string ...$keyword): void
    {
        $this->keyword = $keyword;
    }

    public function setFileSize(?int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function setFileSizeMin(?int $fileSizeMin): void
    {
        $this->fileSizeMin = $fileSizeMin;
    }

    public function setFileSizeMax(?int $fileSizeMax): void
    {
        $this->fileSizeMax = $fileSizeMax;
    }

    public function setMediaType(string ...$mediaType): void
    {
        $this->mediaType = $mediaType;
    }

    public function setMediaWidth(?int $mediaWidth): void
    {
        $this->mediaWidth = $mediaWidth;
    }

    public function setMediaWidthMin(?int $mediaWidthMin): void
    {
        $this->mediaWidthMin = $mediaWidthMin;
    }

    public function setMediaWidthMax(?int $mediaWidthMax): void
    {
        $this->mediaWidthMax = $mediaWidthMax;
    }

    public function setMediaHeight(?int $mediaHeight): void
    {
        $this->mediaHeight = $mediaHeight;
    }

    public function setMediaHeightMin(?int $mediaHeightMin): void
    {
        $this->mediaHeightMin = $mediaHeightMin;
    }

    public function setMediaHeightMax(?int $mediaHeightMax): void
    {
        $this->mediaHeightMax = $mediaHeightMax;
    }

    public function setPostAuthor(int ...$postAuthor): void
    {
        $this->postAuthor = $postAuthor;
    }

    public function setPostParent(int ...$postParent): void
    {
        $this->postParent = $postParent;
    }

    public function setPostAttached(?bool $postAttached): void
    {
        $this->postAttached = $postAttached;
    }

    public function setPostDate($postDate): void
    {
        if (is_string($postDate)) {
            $postDate = new DateTimeImmutable($postDate);
        }
        $this->postDate = $postDate;
    }

    public function setPostDateMin($postDateMin): void
    {
        if (is_string($postDateMin)) {
            $postDateMin = new DateTimeImmutable($postDateMin);
        }
        $this->postDateMin = $postDateMin;
    }

    public function setPostDateMax($postDateMax): void
    {
        if (is_string($postDateMax)) {
            $postDateMax = new DateTimeImmutable($postDateMax);
        }
        $this->postDateMax = $postDateMax;
    }

    public function setPostModified($postModified): void
    {
        if (is_string($postModified)) {
            $postModified = new DateTimeImmutable($postModified);
        }
        $this->postModified = $postModified;
    }

    public function setPostModifiedMin($postModifiedMin): void
    {
        if (is_string($postModifiedMin)) {
            $postModifiedMin = new DateTimeImmutable($postModifiedMin);
        }
        $this->postModifiedMin = $postModifiedMin;
    }

    public function setPostModifiedMax($postModifiedMax): void
    {
        if (is_string($postModifiedMax)) {
            $postModifiedMax = new DateTimeImmutable($postModifiedMax);
        }
        $this->postModifiedMax = $postModifiedMax;
    }

    public function setPostMimeType(string ...$postMimeType): void
    {
        $this->postMimeType = $postMimeType;
    }

    public function setOrder(array $order): void
    {
        $this->order = $order;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @return Generator<Media>
     */
    public function getResults(): Generator
    {
        $results = get_posts($this->buildQuery());
        $results = $this->buildResults($results);
        $results = $this->filterResults($results);
        $results = $this->sortResults($results);
        return $this->limitResults($results);
    }

    private function buildResults(iterable $results): Generator
    {
        foreach ($results as $id) {
            yield new Media($id);
        }
    }

    private function filterResults(iterable $results): Generator
    {
        foreach ($results as $result) {
            if (!$this->isFiltered($result)) {
                yield $result;
            }
        }
    }

    private function sortResults(iterable $results): Generator
    {
        $order = $this->getVirtualOrder();
        if (empty($order)) {
            yield from $results;
            return;
        }

        if ($results instanceof Traversable) {
            $results = iterator_to_array($results);
        }

        foreach ($order as $field => $direction) {
            $function = $this->{self::SORTING_FUNCTIONS[$field]}();
            usort($results, $function);
            if (strtolower($direction) === 'desc') {
                $results = array_reverse($results);
            }
        }

        yield from $results;
    }

    private function limitResults(iterable $results): Generator
    {
        $order = $this->getVirtualOrder();
        if (empty($order)) {
            yield from $results;
            return;
        }

        $count = 0;
        foreach ($results as $result) {
            if ($count >= $this->limit) {
                break;
            }

            yield $result;
            $count++;
        }
    }

    private function getVirtualOrder(): array
    {
        return array_intersect_key($this->order, self::SORTING_FUNCTIONS);
    }

    private function buildQuery(): array
    {
        $query = self::DEFAULT_QUERY;
        $this->buildQueryKeywords($query);
        $this->buildQueryPostParent($query);
        $this->buildQueryPostAuthor($query);
        $this->buildQueryPostDate($query);
        $this->buildQueryPostModified($query);
        $this->buildQueryPostMimeType($query);
        $this->buildQueryOrder($query);
        $this->buildQueryLimit($query);
        return $query;
    }

    private function buildQueryKeywords(array &$query): void
    {
        if (count($this->keyword)) {
            $keywords = array_map(function ($kw) { return '"' . $kw . '"'; }, $this->keyword);
            $query['s'] = implode(' ', $keywords);
        }
    }

    private function buildQueryPostParent(array &$query): void
    {
        if ($this->postParent) {
            $query['post_parent__in'] = $this->postParent;
        } elseif ($this->postAttached === true) {
            $query['post_parent__not_in'] = [0];
        }

        if ($this->postAttached === false) {
            $query['post_parent__in'] = array_merge($query['post_parent__in'] ?? [], [0]);
        }
    }

    private function buildQueryPostAuthor(array &$query): void
    {
        if ($this->postAuthor) {
            $query['author__in'] = $this->postAuthor;
        }
    }

    private function buildQueryPostDate(array &$query): void
    {
        if ($this->postDate) {
            $query['date_query'][] = $this->dateQuery($this->postDate, 'post_date');
        }

        if ($this->postDateMin) {
            $query['date_query'][] = $this->dateQuery($this->postDateMin, 'post_date', '>=');
        }

        if ($this->postDateMax) {
            $query['date_query'][] = $this->dateQuery($this->postDateMax, 'post_date', '<=');
        }
    }

    private function buildQueryPostModified(array &$query): void
    {
        if ($this->postModified) {
            $query['date_query'][] = $this->dateQuery($this->postModified, 'post_modified');
        }

        if ($this->postModifiedMin) {
            $query['date_query'][] = $this->dateQuery($this->postModifiedMin, 'post_modified', '>=');
        }

        if ($this->postModifiedMax) {
            $query['date_query'][] = $this->dateQuery($this->postModifiedMax, 'post_modified', '<=');
        }
    }

    private function dateQuery(DateTimeInterface $date, string $column, string $compare = '='): array
    {
        $date = getdate($date->getTimestamp());
        $query = [
            'year' => (int)$date['year'],
            'month' => (int)$date['mon'],
            'day' => (int)$date['mday'],
            'hour' => (int)$date['hours'],
            'minute' => (int)$date['minutes'],
            'second' => (int)$date['seconds'],
        ];

        if ($compare === '>=') {
            $query = [
                'after' => $query,
                'inclusive' => true,
            ];
        } elseif ($compare === '<=') {
            $query = [
                'before' => $query,
                'inclusive' => true,
            ];
        }

        return $query + ['column' => $column];
    }

    private function buildQueryPostMimeType(array &$query): void
    {
        $mimeTypes = [];
        if ($this->postMimeType) {
            $mimeTypes = $this->postMimeType;
        }

        if ($this->mediaType) {
            $mimeTypes = array_merge(MimeTypes::getTypesByGroup(...$this->mediaType));
        }

        if (count($mimeTypes)) {
            $query['post_mime_type'] = $mimeTypes;
        }
    }

    private function buildQueryOrder(array &$query): void
    {
        $query['orderby'] = array_diff_key($this->order, self::SORTING_FUNCTIONS);
    }

    private function buildQueryLimit(array &$query): void
    {
        $query['posts_per_page'] = -1;
        if ($this->limit === null || $this->limit < 0) {
            return;
        }

        if (empty($this->getVirtualOrder())) {
            $query['posts_per_page'] = $this->limit;
        }
    }

    private function isFiltered(Media $media): bool
    {
        return (
            $this->isFilteredFileSize($media) ||
            $this->isFilteredMediaWidth($media) ||
            $this->isFilteredMediaHeight($media)
        );
    }

    private function isFilteredFileSize(Media $media): bool
    {
        return (
            ($this->fileSize && $this->fileSize !== $media->getFileSize()) ||
            ($this->fileSizeMin && $this->fileSizeMin > $media->getFileSize()) ||
            ($this->fileSizeMax && $this->fileSizeMax < $media->getFileSize())
        );
    }

    private function isFilteredMediaWidth(Media $media): bool
    {
        return (
            ($this->mediaWidth && $this->mediaWidth !== $media->getMediaWidth()) ||
            ($this->mediaWidthMin && $this->mediaWidthMin > $media->getMediaWidth()) ||
            ($this->mediaWidthMax && $this->mediaWidthMax < $media->getMediaWidth())
        );
    }

    private function isFilteredMediaHeight(Media $media): bool
    {
        return (
            ($this->mediaHeight && $this->mediaHeight !== $media->getMediaHeight()) ||
            ($this->mediaHeightMin && $this->mediaHeightMin > $media->getMediaHeight()) ||
            ($this->mediaHeightMax && $this->mediaHeightMax < $media->getMediaHeight())
        );
    }

    private function sortFileSize(): callable
    {
        return function (Media $media1, Media $media2) {
            return $media1->getFileSize() <=> $media2->getFileSize();
        };
    }

    private function sortMediaWidth(): callable
    {
        return function (Media $media1, Media $media2) {
            return $media1->getMediaWidth() <=> $media2->getMediaWidth();
        };
    }

    private function sortMediaHeight(): callable
    {
        return function (Media $media1, Media $media2) {
            return $media1->getMediaHeight() <=> $media2->getMediaHeight();
        };
    }
}