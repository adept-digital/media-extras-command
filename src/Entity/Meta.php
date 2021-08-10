<?php

namespace AdeptDigital\MediaCommands\Entity;

use stdClass;

class Meta
{
    /** @var string */
    private $type;

    /** @var int */
    private $metaId;

    /** @var stdClass */
    private $meta;

    public function __construct(string $type, int $metaId)
    {
        $this->type = $type;
        $this->metaId = $metaId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getObjectId(): int
    {
        return $this->getMeta()->{"{$this->type}_id"};
    }

    public function getKey(): string
    {
        return $this->getMeta()->meta_key;
    }

    public function getValue()
    {
        return $this->getMeta()->meta_value;
    }

    private function getMeta(): stdClass
    {
        if (!isset($this->meta)) {
            $this->meta = get_metadata_by_mid($this->type, $this->metaId);
        }
        return $this->meta;
    }
}