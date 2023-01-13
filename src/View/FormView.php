<?php

namespace Quatrevieux\Form\View;

use ArrayAccess;
use BadMethodCallException;
use Quatrevieux\Form\FormInterface;

/**
 * Structure for the form view
 * Note: Unlike most form components, this class is mutable. So a new instance should be created for each form view.
 *
 * @see FormInterface::view() For creating a new instance
 */
final class FormView implements ArrayAccess
{
    public function __construct(
        /**
         * Form fields indexed by name (or index in case of array)
         *
         * @var array<string|int, FieldView|FormView>
         */
        public readonly array $fields,

        /**
         * Raw HTTP value
         *
         * @var mixed[]
         */
        public readonly array $value,
        // @todo global error ?
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->fields[$offset]);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet(mixed $offset): FieldView|FormView
    {
        return $this->fields[$offset];
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException('FormView is read-only');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException('FormView is read-only');
    }
}
