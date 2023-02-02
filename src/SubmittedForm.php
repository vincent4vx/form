<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;

/**
 * Default implementation of SubmittedFormInterface
 *
 * @template T as object
 * @implements SubmittedFormInterface<T>
 */
final class SubmittedForm extends AbstractFilledForm implements SubmittedFormInterface
{
    /**
     * @var array<string, FieldError|mixed[]>
     */
    private readonly array $errors;

    /**
     * @param FormInterface<T> $form Base form instance
     * @param FormViewInstantiatorInterface|null $viewInstantiator View instantiator. Can be null to disable view system
     * @param mixed[] $httpValue Submitted value
     * @param T $data DTO of submitted value transformed to PHP data
     * @param array<string, FieldError|mixed[]> $errors Errors of submitted value
     */
    public function __construct(FormInterface $form, ?FormViewInstantiatorInterface $viewInstantiator, array $httpValue, object $data, array $errors)
    {
        parent::__construct($form, $viewInstantiator, $data, $httpValue);

        $this->errors = $errors;
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
