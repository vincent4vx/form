<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\Util\Code;
use ReflectionNamedType;
use ReflectionType;
use Stringable;

use function is_scalar;

/**
 * Available cast types
 * To disable cast, use Mixed
 */
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
            self::Int => $value !== '' && is_scalar($value) ? (int) $value : null,
            self::Float => $value !== '' && is_scalar($value) ? (float) $value : null,
            self::String => is_scalar($value) || $value instanceof Stringable ? (string) $value : null,
            self::Bool => is_scalar($value) ? (bool) $value : null,
            self::Object => (object) $value,
            self::Array => (array) $value,
            self::Mixed => $value,
        };
    }

    /**
     * Generate PHP expression for perform cast of the value to the matching type
     * Generated code will behave same as {@see CastType::cast()}
     *
     * @param string $expressionToCast PHP expression to cast
     *
     * @return string Cast expression code
     */
    public function generateCastExpression(string $expressionToCast): string
    {
        $tmpVarName = Code::varName($expressionToCast);

        return match ($this) {
            self::Int => "(($tmpVarName = $expressionToCast) !== '' && is_scalar($tmpVarName) ? (int) $tmpVarName : null)",
            self::Float => "(($tmpVarName = $expressionToCast) !== '' && is_scalar($tmpVarName) ? (float) $tmpVarName : null)",
            self::String => "(is_scalar($tmpVarName = $expressionToCast) || $tmpVarName instanceof \Stringable ? (string) $tmpVarName : null)",
            self::Bool => "(is_scalar($tmpVarName = $expressionToCast) ? (bool) $tmpVarName : null)",
            self::Object => "(($tmpVarName = $expressionToCast) !== null ? (object) $tmpVarName : null)",
            self::Array => "(($tmpVarName = $expressionToCast) !== null ? (array) $tmpVarName : null)",
            self::Mixed => $expressionToCast,
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
            'bool' => self::Bool,
            'array' => self::Array,
            'object' => self::Object,
            default => self::Mixed,
        };
    }
}
