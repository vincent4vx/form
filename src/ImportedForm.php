<?php

namespace Quatrevieux\Form;

final class ImportedForm implements ImportedFormInterface
{
    public function __construct(
        private readonly object $value,
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
