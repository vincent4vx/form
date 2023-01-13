<?php

namespace Quatrevieux\Form\View\Provider;

use Attribute;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;

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
 * @todo empty instance for default ?
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class FieldViewConfiguration implements FieldViewProviderConfigurationInterface, FieldViewProviderInterface
{
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

        if ($this->id) {
            $defaultAttributes['id'] = $this->id;
        }

        if ($this->type) {
            $defaultAttributes['type'] = $this->type;
        }

        return new FieldView(
            $name,
            $value ?? $this->defaultValue,
            $error instanceof FieldError ? $error : null, // @todo handle array of errors?
            $attributes + $defaultAttributes,
        );
    }
}
