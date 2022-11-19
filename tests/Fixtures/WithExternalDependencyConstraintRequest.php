<?php

namespace Quatrevieux\Form\Fixtures;

use Attribute;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\FieldError;

class WithExternalDependencyConstraintRequest
{
    #[ConfiguredLength('foo.length')]
    public string $foo;
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class ConfiguredLength implements ConstraintInterface
{
    public function __construct(
        public readonly string $key,
    ) {
    }

    public function getValidator(ConstraintValidatorRegistryInterface $registry): ConstraintValidatorInterface
    {
        return $registry->getValidator(ConfiguredLengthValidator::class);
    }
}

/**
 * @implements ConstraintValidatorInterface<ConfiguredLength>
 */
class ConfiguredLengthValidator implements ConstraintValidatorInterface
{
    public function __construct(
        private readonly TestConfig $config
    ) {
    }

    public function validate(ConstraintInterface $constraint, mixed $value): ?FieldError
    {
        $len = strlen($value);

        if ($len > $this->config->get($constraint->key)) {
            return new FieldError('Invalid length');
        }

        return null;
    }
}

class TestConfig
{
    public function __construct(
        private readonly array $config
    ) {
    }

    public function get(string $key)
    {
        return $this->config[$key] ?? null;
    }
}
