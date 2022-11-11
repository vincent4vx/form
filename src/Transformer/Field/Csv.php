<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Csv implements FieldTransformerInterface
{
    public function __construct(
        private readonly string $separator = ',',
        private readonly string $enclosure = '',
        private readonly string $escape = '\\',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): mixed
    {
        if (!is_string($value)) {
            return null;
        }

        return str_getcsv($value, $this->separator, $this->enclosure, $this->escape);
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): mixed
    {
        // TODO: Implement transformToHttp() method.
    }
}
