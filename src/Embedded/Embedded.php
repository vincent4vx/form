<?php

namespace Quatrevieux\Form\Embedded;

use Attribute;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;

/**
 * Embedded a form class into a field.
 * Transformers and validators will be added on the field.
 * Extra validators or transformers can be declared before and after this attribute.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // Declare a simple embedded
 *     #[Embedded(MyEmbeddedForm::class)]
 *     public MyEmbeddedForm $embedded;
 *
 *     // Embedded may also be optional. In this case, if the field is not provided on HTTP request,
 *     // validation will be skipped and the field will be set to null
 *     #[Embedded(MyEmbeddedForm::class)]
 *     public ?MyEmbeddedForm $optional;
 *
 *     // Recursive structures are supported
 *     #[Embedded(MyForm::class)]
 *     public ?MyForm $recursive;
 *
 *     // Custom transformers can be added if needed
 *     #[
 *         Json(),
 *         Embedded(UserFilter::class),
 *         LoadUser(),
 *     ]
 *     public User $user;
 * }
 * </code>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Embedded implements ConstraintInterface, DelegatedFieldTransformerInterface
{
    public function __construct(
        /**
         * Embedded DTO class name representing form structure.
         *
         * @var class-string
         */
        public readonly string $class,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        return new EmbeddedValidator($registry->getValidatorFactory());
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return new EmbeddedTransformer($registry->getTransformerFactory(), $registry->getInstantiatorFactory());
    }
}
