<?php

namespace AdeptDigital\MediaCommands\Query;

use AdeptDigital\MediaCommands\Entity\Meta;
use AdeptDigital\MediaCommands\Util\Mysql;
use Generator;
use wpdb;

class MetaQuery
{
    /** @var string */
    private $type;

    /** @var wpdb */
    private $wpdb;

    /** @var int|null */
    private $limit = 1;

    /** @var string */
    private $key = null;

    /** @var string */
    private $value = null;

    /** @var string */
    private $comparison = '=';

    public function __construct(string $type, wpdb $wpdb)
    {
        $this->type = $type;
        $this->wpdb = $wpdb;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function setValue(string $value): void
    {
        $this->value = $value;
    }

    public function setComparison(string $comparison): void
    {
        Mysql::validateComparisonOperator($comparison);
        $this->comparison = $comparison;
    }

    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    public function getResults(): Generator
    {
        $query = $this->buildQuery();
        $results = $this->wpdb->get_col($query);
        return $this->buildResults($results);
    }

    private function buildQuery(): string
    {
        $query = [];
        $this->buildQuerySelect($query);
        $this->buildQueryFrom($query);
        $this->buildQueryWhere($query);
        $this->buildQueryLimit($query);
        return implode("\n", $query);
    }

    private function buildResults(iterable $results): Generator
    {
        foreach ($results as $id) {
            yield new Meta($this->type, $id);
        }
    }

    private function buildQuerySelect(array &$query): void
    {
        $query[] = "SELECT `meta_id`";
    }

    private function buildQueryFrom(array &$query): void
    {
        $table = Mysql::escapeIdentifier("{$this->wpdb->prefix}{$this->type}meta");
        $query[] = "FROM {$table}";
    }

    private function buildQueryWhere(array &$query): void
    {
        $where = [];
        $this->buildWhereKey($where);
        $this->buildWhereValue($where);
        if (count($where) > 0) {
            $query[] = ' WHERE ' . implode(' AND ', $where);
        }
    }

    private function buildWhereKey(array &$where): void
    {
        if ($this->key !== null) {
            $where[] = Mysql::buildComparison('meta_key', $this->key);
        }
    }

    private function buildWhereValue(array &$where): void
    {
        if ($this->value !== null) {
            $where[] = Mysql::buildComparison('meta_value', $this->value, $this->comparison);
        }
    }

    private function buildQueryLimit(array &$query): void
    {
        if (is_int($this->limit)) {
            $query[] = "LIMIT {$this->limit}";
        }
    }
}