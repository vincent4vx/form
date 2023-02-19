<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

/**
 * Type for check an intersection of types.
 * All types must match with the value to be valid.
 */
final class IntersectionType implements TypeInterface
{
    public function __construct(
        /**
         * @var list<TypeInterface>
         */
        private readonly array $types
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return implode('&', array_map(fn (TypeInterface $type) => $type->name(), $this->types));
    }

    /**
     * {@inheritdoc}
     */
    public function check(mixed $value): bool
    {
        foreach ($this->types as $type) {
            if (!$type->check($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCheck(string $value): string
    {
        return implode(' && ', array_map(fn (TypeInterface $type) => "({$type->generateCheck($value)})", $this->types));
    }
}
