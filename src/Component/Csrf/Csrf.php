<?php

namespace Quatrevieux\Form\Component\Csrf;

use Attribute;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;

/**
 * Add a token to the form to prevent CSRF attacks
 *
 * To use this constraint, you must have the Symfony Security component installed (i.e. "symfony/security-csrf"),
 * and register the {@see CsrfManager} service in the form registry.
 *
 * The CSRF token can be regenerated on each request by setting the "refresh" option to true.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // The CSRF token will be generated once per session
 *     // Required constraint as no effect here : csrf token is always validated
 *     #[Csrf]
 *     public string $csrf;
 *
 *     // The CSRF token will be regenerated on each request
 *     #[Csrf(refresh: true)]
 *     public string $csrfRefresh;
 * }
 *
 * // Add CsrfManager to the form registry (use PSR-11 container in this example)
 * $container->register(new CsrfManager($container->get(CsrfTokenManagerInterface::class)));
 * $registry = new ContainerRegistry($container);
 * $factory = DefaultFormFactory::create($registry);
 *
 * // Create and submit the form
 * $form = $factory->create(MyForm::class);
 * $submitted = $form->submit(...);
 * </code>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Csrf implements ConstraintInterface, DelegatedFieldTransformerInterface, FieldViewProviderConfigurationInterface
{
    public const CODE = '642ecf60-c56e-547b-9064-dd30d553f5dd';

    public function __construct(
        /**
         * The CSRF token id
         * Can be any arbitrary string
         */
        public readonly string $id = 'form',

        /**
         * If true, the token will be regenerated on each request
         * By default, the token is only generated once per session
         */
        public readonly bool $refresh = false,

        /**
         * Error message
         */
        public readonly string $message = 'Invalid CSRF token',
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getValidator(RegistryInterface $registry): ConstraintValidatorInterface
    {
        return $registry->getConstraintValidator(CsrfManager::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return $registry->getFieldTransformer(CsrfManager::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getViewProvider(RegistryInterface $registry): FieldViewProviderInterface
    {
        /** @var FieldViewProviderInterface<Csrf> */
        return $registry->getFieldTransformer(CsrfManager::class);
    }
}
