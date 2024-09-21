<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;

use function is_scalar;
use function trim;

/**
 * Trim the field value
 *
 * This transformer will remove all spaces at the beginning and at the end of the value.
 * The transformation is only applied when transforming from HTTP.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     #[Trim]
 *     public string $name;
 * }
 * </code>
 *
 * @implements FieldTransformerGeneratorInterface<Trim>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Trim implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        return trim((string) $value);
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
    public function canThrowError(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return Code::expr($previousExpression)->storeAndFormat('is_scalar({}) ? trim((string) {}) : null');
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return $previousExpression;
    }
}
