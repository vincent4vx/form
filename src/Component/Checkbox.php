<?php

namespace Quatrevieux\Form\Component;

use Attribute;
use Closure;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\Generator\FieldViewProviderGeneratorInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

use function is_scalar;

/**
 * Handle HTTP checkbox input
 *
 * The field value is true when a value is present in the HTTP request and is equal to the given httpValue (default to "1").
 * The field value is false when the value is not present in the HTTP request or is not equal to the given httpValue.
 * So, the field value is always a non-nullable boolean.
 *
 * Note: the http value will be cast to string before comparison.
 *
 * @implements FieldViewProviderInterface<Checkbox>
 * @implements FieldViewProviderGeneratorInterface<Checkbox>
 * @implements FieldTransformerGeneratorInterface<Checkbox>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Checkbox implements FieldTransformerInterface, FieldTransformerGeneratorInterface, FieldViewProviderConfigurationInterface, FieldViewProviderInterface, FieldViewProviderGeneratorInterface
{
    public function __construct(
        private readonly string $httpValue = '1',
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): bool
    {
        return is_scalar($value) && (string) $value === $this->httpValue;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): ?string
    {
        if ($value === true) {
            return $this->httpValue;
        }

        return null;
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
    public function getViewProvider(RegistryInterface $registry): FieldViewProviderInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function view(FieldViewProviderConfigurationInterface $configuration, string $name, mixed $value, array|FieldError|null $error, array $attributes): FieldView
    {
        $attributes['type'] ??= 'checkbox';

        if ($configuration->transformFromHttp($value) === true) {
            $attributes['checked'] = true;
        }

        // Remove the required attribute : a checkbox field always have a value (true or false)
        unset($attributes['required']);

        return new FieldView(
            $name,
            $configuration->httpValue,
            $error instanceof FieldError ? $error : null,
            $attributes
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return Code::expr($previousExpression)->storeAndFormat(
            'is_scalar({}) && (string) {} === {httpValue}',
            httpValue: $transformer->httpValue
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return Code::expr($previousExpression)->format(
            '({}) === true ? {httpValue} : null',
            httpValue: $transformer->httpValue
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateFieldViewExpression(FieldViewProviderConfigurationInterface $configuration, string $name, array $attributes): Closure
    {
        $attributes['type'] ??= 'checkbox';

        // Remove the required attribute : a checkbox field always have a value (true or false)
        unset($attributes['required']);

        return static fn (string $valueAccessor, string $errorAccessor, ?string $rootFieldNameAccessor) => Code::new(FieldView::class, [
            $rootFieldNameAccessor ? Code::raw('"{' . $rootFieldNameAccessor. '}[' . $name . ']"') : $name,
            $configuration->httpValue,
            Code::expr($errorAccessor)->isInstanceOfOr(FieldError::class, null),
            $attributes + [
                'checked' => Code::expr($valueAccessor)->storeAndFormat(
                    'is_scalar({}) && (string) {} === {httpValue}',
                    httpValue: $configuration->httpValue
                ),
            ]
        ]);
    }
}
