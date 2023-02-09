<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use Quatrevieux\Form\DataMapper\DataMapperInterface;
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
         * @var DataMapperInterface<T>
         */
        private readonly DataMapperInterface $dataMapper,

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
        $dto = $this->dataMapper->toDataObject($transformation->values);
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
            $this->transformer->transformToHttp($this->dataMapper->toArray($data)),
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
