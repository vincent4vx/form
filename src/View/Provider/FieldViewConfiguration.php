<?php

namespace Quatrevieux\Form\View\Provider;

use Attribute;
use Closure;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\Generator\FieldViewProviderGeneratorInterface;

/**
 * Default configuration for the field view generation
 * It will instance a simple {@see FieldView} object, with a configurable default value and attributes.
 *
 * Usage:
 * <code>
 * final class MyForm
 * {
 *     #[FieldViewConfiguration(type: 'number', attributes: ['min' => 0, 'max' => 100, 'step' => 5])]
 *     public int $count;
 * }
 * </code>
 *
 * @implements FieldViewProviderInterface<self>
 * @implements FieldViewProviderGeneratorInterface<self>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class FieldViewConfiguration implements FieldViewProviderConfigurationInterface, FieldViewProviderInterface, FieldViewProviderGeneratorInterface
{
    private static ?FieldViewConfiguration $default = null;

    public function __construct(
        /**
         * The HTML input type
         */
        public readonly ?string $type = null,

        /**
         * Define the "id" attribute of the input
         */
        public readonly ?string $id = null,

        /**
         * Define the value to set if the field is not submitted
         */
        public readonly mixed $defaultValue = null,

        /**
         * Will configure default attributes on the {@see FieldView} object
         *
         * The key is the HTML attribute name, and the value is the attribute value.
         * To use flag attributes (e.g. "required"), use a boolean value.
         *
         * @var array<string, scalar>
         * @see FieldView::$attributes
         */
        public readonly array $attributes = [],
    ) {
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
        $defaultAttributes = $configuration->attributes;

        if ($configuration->id) {
            $defaultAttributes['id'] = $configuration->id;
        }

        if ($configuration->type) {
            $defaultAttributes['type'] = $configuration->type;
        }

        return new FieldView(
            $name,
            $value ?? $configuration->defaultValue,
            $error instanceof FieldError ? $error : null,
            $attributes + $defaultAttributes,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateFieldViewExpression(FieldViewProviderConfigurationInterface $configuration, string $name, array $attributes): Closure
    {
        $attributes += $configuration->attributes;

        if ($configuration->id) {
            $attributes['id'] ??= $configuration->id;
        }

        if ($configuration->type) {
            $attributes['type'] ??= $configuration->type;
        }

        return static fn (string $valueAccessor, string $errorAccessor, ?string $rootFieldNameAccessor) => Code::new(FieldView::class, [
            $rootFieldNameAccessor ? Code::raw('"{' . $rootFieldNameAccessor. '}[' . $name . ']"') : $name,
            $configuration->defaultValue !== null ? Code::raw($valueAccessor . ' ?? ' . Code::value($configuration->defaultValue)) : Code::raw($valueAccessor),
            Code::expr($errorAccessor)->isInstanceOfOr(FieldError::class, null),
            $attributes
        ]);
    }

    /**
     * Get the default (i.e. empty) view configuration instance
     */
    public static function default(): FieldViewConfiguration
    {
        return self::$default ??= new FieldViewConfiguration();
    }
}
