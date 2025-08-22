<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use function count;
use function explode;
use function strtolower;
use function substr;

/**
 * Parse a type string.
 */
final class TypeParser
{
    /**
     * Parse a disjunctive normal form type string.
     *
     * The type string can be a combination of atomic types separated by | (union) and & (intersection).
     * The intersection operator has a higher precedence than the union operator, so parentheses are optional.
     *
     * Examples:
     * - int : create a primitive type of int : `PrimitiveType::Int`
     * - int|float : create a union type of int and float : `new UnionType([PrimitiveType::Int, PrimitiveType::Float])`
     * - Foo&Bar : create an intersection type of int and float : `new IntersectionType([new ClassType(Foo::class), new ClassType(Bar::class)])`
     * - Foo&Bar|float : the value may be a float, or implements Foo and Bar
     *
     * @param string $type The type string
     * @return TypeInterface
     */
    public static function parse(string $type): TypeInterface
    {
        $types = array_map(fn(string $type) => self::parseIntersection($type), explode('|', $type));

        if (count($types) === 1) {
            return $types[0];
        }

        return new UnionType($types);
    }

    private static function parseIntersection(string $type): TypeInterface
    {
        if ($type[0] === '(' && $type[-1] === ')') {
            $type = substr($type, 1, -1);
        }

        $types = array_map(fn(string $type) => self::parseAtomic($type), explode('&', $type));

        if (count($types) === 1) {
            return $types[0];
        }

        return new IntersectionType($types);
    }

    private static function parseAtomic(string $type): TypeInterface
    {
        // @phpstan-ignore-next-line : assume that $type is a class if it is not a primitive type
        return PrimitiveType::tryFrom(strtolower($type)) ?? new ClassType($type);
    }
}
