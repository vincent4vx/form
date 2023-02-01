<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;

/**
 * @template T as object
 *
 * @implements SubmittedFormInterface<T>
 */
final class SubmittedForm implements SubmittedFormInterface
{
    public function __construct(
        /**
         * Raw submitted HTTP data
         *
         * @var array<string, mixed>
         */
        private readonly array $httpValue,

        /**
         * @var T
         */
        private readonly object $data,

        /**
         * @var array<string, FieldError|mixed[]>
         */
        private readonly array $errors,
        private readonly ?FormViewInstantiatorInterface $viewInstantiator = null,
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

    /**
     * {@inheritdoc}
     */
    public function view(): FormView
    {
        $viewInstantiator = $this->viewInstantiator ?? throw new BadMethodCallException('View system disabled for the form');

        return $viewInstantiator->submitted($this->httpValue, $this->errors);
    }
}
