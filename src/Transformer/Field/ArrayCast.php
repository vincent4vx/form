<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Util\Code;

/**
 * Cast values of an array
 *
 * The performed cast is a fail-safe operation :
 * - if the value cannot be cast, `null` will be returned
 * - in case of numeric type, invalid string will return 0 (or 0.0 on float)
 *
 * Transformation to HTTP value will simply cast non-null value to array.
 *
 * @see CastType List of available types
 * @see Cast For cast a scalar value
 *
 * @implements FieldTransformerGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayCast implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        /**
         * Type of array elements
         */
        private readonly CastType $elementType,

        /**
         * Keep original keys ?
         *
         * If true, original keys will be kept.
         * If false, transformed array will be converted to a list (i.e. sequential keys).
         */
        private readonly bool $preserveKeys = true,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @return mixed[]|null
     */
    public function transformFromHttp(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $value = (array) $value;
        $transformed = [];
        $type = $this->elementType;

        if ($this->preserveKeys) {
            foreach ($value as $key => $item) {
                $transformed[$key] = $type->cast($item);
            }
        } else {
            foreach ($value as $item) {
                $transformed[] = $type->cast($item);
            }
        }

        return $transformed;
    }

    /**
     * {@inheritdoc}
     *
     * @return null|mixed[]
     */
    public function transformToHttp(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        return (array) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression): string
    {
        $elementTransformationExpression = $transformer->elementType->generateCastExpression('$value');
        $expressionVarName = Code::varName($previousExpression);

        if ($transformer->preserveKeys) {
            // Use array_map for cast values
            return "(($expressionVarName = $previousExpression) !== null ? array_map(static fn (\$value) => $elementTransformationExpression, (array) $expressionVarName) : null)";
        } else {
            // Simple foreach is more performant than array_map. Generated code should be wrapped into a closure to create a value expression.
            return "(($expressionVarName = $previousExpression) !== null ? (static function (\$values) { \$r = []; foreach (\$values as \$value) { \$r[] = $elementTransformationExpression; } return \$r; })((array) $expressionVarName) : null)";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression): string
    {
        $expressionVarName = Code::varName($previousExpression);
        return "(($expressionVarName = $previousExpression) !== null ? (array) $expressionVarName : null)";
    }
}
