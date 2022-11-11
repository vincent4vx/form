<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Validator\FieldError;

/**
 * @template T as object
 */
final class SubmittedForm implements SubmittedFormInterface
{
    /**
     * @var T
     */
    private readonly object $data;

    /**
     * @var array<string, FieldError>
     */
    private readonly array $errors;

    /**
     * @param T $data
     * @param array<string, FieldError> $errors
     */
    public function __construct(object $data, array $errors)
    {
        $this->data = $data;
        $this->errors = $errors;
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
