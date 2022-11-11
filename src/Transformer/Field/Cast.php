<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Cast implements FieldTransformerInterface
{
    public function __construct(
        private readonly CastType $type
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): mixed
    {
        return $this->type->cast($value);
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return (array) $value;
    }
}
