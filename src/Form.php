<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\Validator\ValidatorInterface;

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

        return new SubmittedForm($dto, $errors);
    }

    /**
     * {@inheritdoc}
     */
    public function import(object $data): ImportedFormInterface
    {
        return new ImportedForm(
            $data,
            $this->transformer->transformToHttp($this->instantiator->export($data))
        );
    }
}
