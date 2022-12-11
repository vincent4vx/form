<?php

namespace Quatrevieux\Form\Fixtures;

use Attribute;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\TransformationError;

class FailingTransformerRequest
{
    #[UnsafeJsonTransformer]
    public object $foo;

    #[UnsafeBase64]
    #[TransformationError(message: 'invalid data', keepOriginalValue: true)]
    public ?string $customTransformerErrorHandling;

    #[UnsafeBase64]
    #[TransformationError(ignore: true)]
    public ?string $ignoreError;
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

#[Attribute(Attribute::TARGET_PROPERTY)]
class UnsafeBase64 implements FieldTransformerInterface
{
    public function transformFromHttp(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        $value = base64_decode($value, true);

        if ($value === false) {
            throw new \InvalidArgumentException('Invalid base64');
        }

        return $value;
    }

    public function transformToHttp(mixed $value): mixed
    {
        if (!$value) {
            return null;
        }

        return base64_encode($value);
    }
    public function canThrowError(): bool
    {
        return true;
    }
}
