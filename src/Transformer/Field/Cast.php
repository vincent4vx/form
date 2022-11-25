<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Util\Code;

/**
 * Cast HTTP value to target type
 *
 * This transformer is automatically added on typed properties
 * The performed cast is a fail-safe operation :
 * - if the value cannot be cast, `null` will be returned
 * - in case of numeric type, invalid string will return 0 (or 0.0 on float)
 *
 * Transformation to HTTP value will simply cast non-scalar types to array, and let untransformed any other values
 *
 * @see CastType List of available types
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Cast implements FieldTransformerInterface, FieldTransformerGeneratorInterface
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
    public function transformToHttp(mixed $value): string|array|bool|int|null|float
    {
        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return $value;
        }

        return (array) $value;
    }

    /**
     * {@inheritdoc}
     *
     * @param Cast $transformer
     */
    public function generateTransformFromHttp(FieldTransformerInterface $transformer, string $previousExpression): string
    {
        return $transformer->type->generateCastExpression($previousExpression);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(FieldTransformerInterface $transformer, string $previousExpression): string
    {
        $expressionVarName = Code::varName($previousExpression);
        return "(($expressionVarName = $previousExpression) === null || is_scalar($expressionVarName) ? $expressionVarName : (array) $expressionVarName)";
    }
}
