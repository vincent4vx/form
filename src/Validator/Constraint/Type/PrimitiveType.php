<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_object;
use function is_string;

/**
 * Primitive PHP types.
 * Those types are atomic types handled natively by PHP.
 */
enum PrimitiveType: string implements TypeInterface
{
    case Int = 'int';
    case Float = 'float';
    case String = 'string';
    case Bool = 'bool';
    case Object = 'object';
    case Array = 'array';
    case Mixed = 'mixed';
    case Null = 'null';
    case True = 'true';
    case False = 'false';

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function check(mixed $value): bool
    {
        return match ($this) {
            self::Int => is_int($value),
            self::Float => is_float($value),
            self::String => is_string($value),
            self::Bool => is_bool($value),
            self::Object => is_object($value),
            self::Array => is_array($value),
            self::Mixed => true,
            self::Null => $value === null,
            self::True => $value === true,
            self::False => $value === false,
        };
    }

    /**
     * {@inheritdoc}
     */
    public function generateCheck(string $value): string
    {
        return match ($this) {
            self::Int => 'is_int(' . $value . ')',
            self::Float => 'is_float(' . $value . ')',
            self::String => 'is_string(' . $value . ')',
            self::Bool => 'is_bool(' . $value . ')',
            self::Object => 'is_object(' . $value . ')',
            self::Array => 'is_array(' . $value . ')',
            self::Mixed => 'true',
            self::Null => $value . ' === null',
            self::True => $value . ' === true',
            self::False => $value . ' === false',
        };
    }
}
