<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use function array_map;
use function implode;

/**
 * Type for check an union of types.
 * At least one type must match with the value to be valid.
 */
final class UnionType implements TypeInterface
{
    public function __construct(
        /**
         * @var list<TypeInterface>
         */
        public readonly array $types
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return implode('|', array_map(fn (TypeInterface $type) => $type->name(), $this->types));
    }

    /**
     * {@inheritdoc}
     */
    public function check(mixed $value): bool
    {
        foreach ($this->types as $type) {
            if ($type->check($value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCheck(string $value): string
    {
        return implode(' || ', array_map(fn (TypeInterface $type) => "({$type->generateCheck($value)})", $this->types));
    }
}
