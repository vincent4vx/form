<?php

namespace Quatrevieux\Form\View;

use ArrayAccess;
use BadMethodCallException;
use Countable;
use IteratorAggregate;
use Quatrevieux\Form\FormInterface;
use Quatrevieux\Form\Validator\FieldError;
use Traversable;

use function count;

/**
 * Structure for the form view
 * Note: Unlike most form components, this class is mutable. So a new instance should be created for each form view.
 *
 * @implements ArrayAccess<array-key, FieldView|FormView>
 * @implements IteratorAggregate<array-key, FieldView|FormView>
 *
 * @see FormInterface::view() For creating a new instance
 */
final class FormView implements ArrayAccess, IteratorAggregate, Countable
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
        public readonly array $value = [],

        /**
         * Template element view in case of array
         *
         * @var FormView|FieldView|null
         */
        public readonly FormView|FieldView|null $template = null,

        /**
         * Global form error
         *
         * @var FieldError|null
         */
        public ?FieldError $error = null,
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

    /**
     * {@inheritdoc}
     */
    public function getIterator(): Traversable
    {
        yield from $this->fields;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return count($this->fields);
    }
}
