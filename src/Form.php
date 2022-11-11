<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Validator\ValidatorInterface;

/**
 * @template T as object
 * @implements FormInterface<T>
 */
final class Form implements FormInterface
{
    public function __construct(
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
        $dto = $this->instantiator->instantiate($data);
        $errors = $this->validator->validate($dto);

        return new SubmittedForm($dto, $errors);
    }
}
