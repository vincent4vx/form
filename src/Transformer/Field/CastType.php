<?php

namespace Quatrevieux\Form\Transformer\Field;

use ReflectionNamedType;
use ReflectionType;
use Stringable;

enum CastType
{
    case Int;
    case Float;
    case String;
    case Bool;
    case Object;
    case Array;
    case Mixed;

    /**
     * Perform cast of the value to the matching type
     *
     * If value is null, the returned value is null
     * If the value cannot be cast to the requested type, null will be returned
     *
     * @param mixed $value Value to cast
     *
     * @return mixed Casted value
     */
    public function cast(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($this) {
            self::Int => is_scalar($value) ? (int) $value : null,
            self::Float => is_scalar($value) ? (float) $value : null,
            self::String => is_scalar($value) || $value instanceof Stringable ? (string) $value : null,
            self::Bool => is_scalar($value) ? (bool) $value : null,
            self::Object => (object) $value,
            self::Array => (array) $value,
            self::Mixed => $value,
        };
    }

    /**
     * Get CastType enum value corresponding to declared ReflectionType
     * Will return `CastType::Mixed` is type cannot be resolved
     *
     * @param ReflectionType $type ReflectionType to resolve
     *
     * @return CastType Matching cast type or Mixed
     */
    public static function fromReflectionType(ReflectionType $type): CastType
    {
        if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return self::Mixed;
        }

        return match ($type->getName()) {
            'string' => self::String,
            'int' => self::Int,
            'float' => self::Float,
            'boolean' => self::Bool,
            'array' => self::Array,
            'object' => self::Object,
            default => self::Mixed,
        };
    }
}
