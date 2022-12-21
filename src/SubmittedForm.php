<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Validator\FieldError;

/**
 * @template T as object
 *
 * @implements SubmittedFormInterface<T>
 */
final class SubmittedForm implements SubmittedFormInterface
{
    public function __construct(
        /**
         * @var T
         */
        private readonly object $data,

        /**
         * @var array<string, FieldError|mixed[]>
         */
        private readonly array $errors
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function value(): object
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        return empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
