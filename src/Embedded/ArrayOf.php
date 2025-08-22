<?php

namespace Quatrevieux\Form\Embedded;

use Attribute;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

/**
 * Create an array of embedded forms.
 * Transformers and validators will be added on the field.
 * Extra validators or transformers can be declared before and after this attribute.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *      // Declare a simple array of embedded
 *     #[ArrayOf(MyEmbeddedForm::class)]
 *     public array $embedded;
 *
 *     // Extra transformers or validators can be added
 *     #[Json]
 *     #[ArrayOf(MyEmbeddedForm::class)]
 *     public array $embedded;
 * }
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class ArrayOf implements ConstraintInterface, DelegatedFieldTransformerInterface, FieldViewProviderConfigurationInterface
{
    public function __construct(
        /**
         * Embedded DTO class name representing form structure.
         *
         * @var class-string
         */
        public readonly string $class,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        return new ArrayOfValidator($registry);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return new ArrayOfTransformer($registry);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewProvider(RegistryInterface $registry): FieldViewProviderInterface
    {
        return new ArrayOfViewProvider($registry->getFormViewInstantiatorFactory());
    }
}
