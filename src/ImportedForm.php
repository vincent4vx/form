<?php

namespace Quatrevieux\Form;

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
}
