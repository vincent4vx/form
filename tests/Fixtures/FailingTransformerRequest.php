<?php

namespace Quatrevieux\Form\Fixtures;

use Attribute;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;

class FailingTransformerRequest
{
    #[UnsafeJsonTransformer]
    public object $foo;
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class UnsafeJsonTransformer implements FieldTransformerInterface
{
    /**
     * @inheritDoc
     */
    public function transformFromHttp(mixed $value): mixed
    {
        $o = json_decode($value, false, 512, JSON_THROW_ON_ERROR);

        if (!is_object($o)) {
            throw new \InvalidArgumentException('Invalid JSON object');
        }

        return $o;
    }

    /**
     * @inheritDoc
     */
    public function transformToHttp(mixed $value): mixed
    {
        return json_encode($value);
    }

    /**
     * @inheritDoc
     */
    public function canThrowError(): bool
    {
        return true;
    }
}
