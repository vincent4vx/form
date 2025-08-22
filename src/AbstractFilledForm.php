<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;

use function array_replace_recursive;

/**
 * Base implementation of FilledFormInterface
 *
 * @template T as object
 * @implements FilledFormInterface<T>
 */
abstract class AbstractFilledForm implements FilledFormInterface
{
    public function __construct(
        /**
         * Base form instance
         *
         * @var FormInterface<T>
         */
        private readonly FormInterface $form,

        /**
         * View instantiator
         * Can be null to disable view system
         *
         * @var FormViewInstantiatorInterface|null
         */
        protected readonly ?FormViewInstantiatorInterface $viewInstantiator,

        /**
         * Imported value
         *
         * @var T
         */
        protected readonly object $value,

        /**
         * Value transformed to HTTP data
         *
         * @var mixed[]
         */
        protected readonly array $httpValue,
    ) {}

    /**
     * {@inheritdoc}
     */
    final public function submit(array $data): SubmittedFormInterface
    {
        return $this->form->submit(array_replace_recursive($this->httpValue, $data));
    }

    /**
     * {@inheritdoc}
     */
    final public function import(object $data): ImportedFormInterface
    {
        return $this->form->import($data);
    }

    /**
     * {@inheritdoc}
     */
    final public function value(): object
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    final public function httpValue(): array
    {
        return $this->httpValue;
    }
}
