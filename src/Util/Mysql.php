<?php

namespace AdeptDigital\MediaCommands\Util;

use InvalidArgumentException;
use LogicException;

class Mysql
{
    private const COMPARISON_OPERATORS = [
        '=',
        '!=',
        '<>',
        '>',
        '>=',
        '<',
        '<=',
        'LIKE',
        'NOT LIKE',
        'RLIKE',
        'NOT RLIKE',
    ];

    private const MATCH_IDENTIFIER = '/^[a-z][a-z0-9_-]*$/i';

    public static function buildComparison(string $identifier, $value, string $operator = '='): string
    {
        if (!is_scalar($value)) {
            throw new LogicException('Comparison of non-scalar values is not implemented.');
        }
        return self::escapeIdentifier($identifier) . " {$operator} " . self::escapeValue($value);
    }

    public static function validateComparisonOperator(string $operator): void
    {
        $operator = strtoupper($operator);
        if (!in_array($operator, self::COMPARISON_OPERATORS)) {
            $allowed = implode('|', self::COMPARISON_OPERATORS);
            throw new InvalidArgumentException("Comparison operator `{$operator}` does not match `({$allowed})`.");
        }
    }

    public static function escapeIdentifier(string $identifier): string
    {
        if (!preg_match(self::MATCH_IDENTIFIER, $identifier)) {
            throw new InvalidArgumentException("Invalid identifier `{$identifier}`.");
        }
        return "`{$identifier}`";
    }

    public static function escapeValue($value): string
    {
        if (is_null($value)) {
            return 'NULL';
        } elseif (is_int($value) || is_float($value)) {
            return $value;
        } else {
            return "'" . esc_sql((string)$value) . "'";
        }
    }
}