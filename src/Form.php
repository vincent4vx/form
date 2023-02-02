<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\Validator\ValidatorInterface;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;

/**
 * Base form implementation
 *
 * @template T as object
 * @implements FormInterface<T>
 */
final class Form implements FormInterface
{
    public function __construct(
        /**
         * @var FormTransformerInterface
         */
        private readonly FormTransformerInterface $transformer,

        /**
         * @var InstantiatorInterface<T>
         */
        private readonly InstantiatorInterface $instantiator,

        /**
         * @var ValidatorInterface<T>
         */
        private readonly ValidatorInterface $validator,

        /**
         * @var FormViewInstantiatorInterface|null
         */
        private readonly ?FormViewInstantiatorInterface $viewInstantiator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function submit(array $data): SubmittedFormInterface
    {
        $transformation = $this->transformer->transformFromHttp($data);
        $dto = $this->instantiator->instantiate($transformation->values);
        $errors = $this->validator->validate($dto, $transformation->errors);

        return new SubmittedForm(
            $this,
            $this->viewInstantiator,
            $data,
            $dto,
            $errors,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function import(object $data): ImportedFormInterface
    {
        /** @var ImportedForm<T> */
        return new ImportedForm(
            $this,
            $this->viewInstantiator,
            $data,
            $this->transformer->transformToHttp($this->instantiator->export($data)),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function view(): FormView
    {
        $viewInstantiator = $this->viewInstantiator ?? throw new BadMethodCallException('View system disabled for the form');

        return $viewInstantiator->default();
    }
}
