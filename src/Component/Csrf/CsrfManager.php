<?php

namespace Quatrevieux\Form\Component\Csrf;

use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

use function is_scalar;

/**
 * Wrap the Symfony CSRF token manager {@see CsrfTokenManagerInterface} to use it with {@see Csrf} field.
 *
 * @implements ConfigurableFieldTransformerInterface<Csrf>
 * @implements ConstraintValidatorInterface<Csrf>
 * @implements ConstraintValidatorGeneratorInterface<Csrf>
 * @implements FieldTransformerGeneratorInterface<Csrf>
 * @implements FieldViewProviderInterface<Csrf>
 */
final class CsrfManager implements ConfigurableFieldTransformerInterface, FieldViewProviderInterface, ConstraintValidatorInterface, ConstraintValidatorGeneratorInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $tokenManager,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): FieldError|null
    {
        /** @var string $value Transformer always transform this value to string */
        $valid = $constraint->refresh
            ? $this->validateTokenAndRemove($constraint->id, $value)
            : $this->validateToken($constraint->id, $value)
        ;

        if ($valid) {
            return null;
        }

        return new FieldError($constraint->message, code: Csrf::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): string
    {
        return empty($value) || !is_scalar($value) ? '__empty__' : (string) $value;
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return $value; // No transformation : token generation is performed during view instantiation
    }

    /**
     * {@inheritdoc}
     */
    public function view(FieldViewProviderConfigurationInterface $configuration, string $name, mixed $value, array|FieldError|null $error, array $attributes): FieldView
    {
        $attributes['type'] ??= 'hidden';

        // Force usage of actual token value
        $value = $configuration->refresh
            ? $this->tokenManager->refreshToken($configuration->id)->getValue()
            : $this->tokenManager->getToken($configuration->id)->getValue()
        ;

        return new FieldView(
            $name,
            $value,
            $error instanceof FieldError ? $error : null,
            $attributes
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $manager = Expr::this()->registry->getConstraintValidator(self::class);
        $error = Expr::new(FieldError::class, [$constraint->message, [], Csrf::CODE]);

        return FieldErrorExpression::single(function (string $accessor) use ($error, $constraint, $manager) {
            $accessor = Code::raw($accessor);
            $validation = $constraint->refresh
                ? $manager->validateTokenAndRemove($constraint->id, $accessor)
                : $manager->validateToken($constraint->id, $accessor)
            ;

            return $validation->format('{} ? null : {error}', error: $error);
        });
    }

    /**
     * @internal
     */
    public function validateToken(string $tokenId, string $value): bool
    {
        return $this->tokenManager->isTokenValid(new CsrfToken($tokenId, $value));
    }

    /**
     * @internal
     */
    public function validateTokenAndRemove(string $tokenId, string $value): bool
    {
        $valid = $this->validateToken($tokenId, $value);
        $this->tokenManager->removeToken($tokenId);

        return $valid;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return Code::expr($previousExpression)->storeAndFormat("empty({}) || !is_scalar({}) ? '__empty__' : (string) {}");
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        return $previousExpression;
    }
}
