<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

/**
 * Cast HTTP value to target type
 *
 * This transformer is automatically added on typed properties
 * The performed cast is a fail-safe operation :
 * - if the value cannot be cast, `null` will be returned
 * - in case of numeric type, invalid string will return 0 (or 0.0 on float)
 *
 * Transformation to HTTP value will simply assume the value is already a normalized value
 *
 * @see CastType List of available types
 *
 * @implements FieldTransformerGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Cast implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly CastType $type,
    ) {}

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
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return $transformer->type->generateCastExpression($previousExpression);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return $previousExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return false;
    }
}
