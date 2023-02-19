<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

/**
 * Type for check if a value is an instance of a class or an interface.
 */
final class ClassType implements TypeInterface
{
    public function __construct(
        /**
         * @var class-string
         */
        private readonly string $class
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function name(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function check(mixed $value): bool
    {
        return $value instanceof $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function generateCheck(string $value): string
    {
        return $value . ' instanceof \\' . $this->class;
    }
}
