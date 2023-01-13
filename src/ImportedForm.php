<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;

/**
 * @template T as object
 * @implements ImportedFormInterface<T>
 */
final class ImportedForm implements ImportedFormInterface
{
    public function __construct(
        /**
         * @var T
         */
        private readonly object $value,

        /**
         * @var mixed[]
         */
        private readonly array $httpValue,
        private readonly FormViewInstantiatorInterface $viewInstantiator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function value(): object
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function httpValue(): array
    {
        return $this->httpValue;
    }

    /**
     * {@inheritdoc}
     */
    public function view(): FormView
    {
        return $this->viewInstantiator->submitted($this->httpValue, []);
    }
}
