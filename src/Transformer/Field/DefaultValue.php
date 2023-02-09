<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Code;

use function str_replace;

/**
 * Defines a default value for a field.
 *
 * The default value is used when the field is not submitted.
 * This transformer will be automatically added to the field if the default value is not null.
 * You can define this transformer explicitly to ignore the default behavior.
 *
 * Note: Be careful of the transformer order. If this attribute is defined before other transformers, the value should be an HTTP (i.e. not transformed) value.
 *       If this attribute is defined after other transformers, the value should be a PHP (i.e. transformed) value.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     public int $implicit = 42; // Implicitly define the default value. Will be applied after all other transformers.
 *
 *     #[DefaultValue(12.3)] // Explicitly define the default value. Default property value will be ignored.
 *     public float $explicit = 0.0;
 *
 *     #[DefaultValue('foo,bar')] // When defined before other transformers, the value should be an HTTP value.
 *     #[Csv]
 *     public array $values;
 * }
 * </code>
 *
 * @implements FieldTransformerGeneratorInterface<DefaultValue>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DefaultValue implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly mixed $value,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): mixed
    {
        return $value ?? $this->value;
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
        if ($transformer->value === null) {
            return $previousExpression;
        }

        $expression = $previousExpression . ' ?? ' . Code::value($transformer->value);

        // Remove useless null coalescing with null
        return str_replace(' ?? null ?? ', ' ?? ', $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return $previousExpression;
    }
}
