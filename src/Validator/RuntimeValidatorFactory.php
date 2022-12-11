<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\Constraint\Required;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;

use function array_unshift;

/**
 * Loads a {@see RuntimeValidator} instance depending on defined attributes on fields
 * By default, {@see Required} is added on non-nullable fields
 */
final class RuntimeValidatorFactory implements ValidatorFactoryInterface
{
    public function __construct(
        private readonly ConstraintValidatorRegistryInterface $validatorRegistry
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @param class-string<T> $dataClass
     * @template T as object
     * @return ValidatorInterface<T>
     *
     * @todo null validator for optimisations
     */
    public function create(string $dataClass): ValidatorInterface
    {
        $reflectionClass = new ReflectionClass($dataClass);
        $fieldsConstraints = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $requiredShouldBeAdded = ($type = $property->getType()) && !$type->allowsNull();
            $fieldConstraints = [];

            foreach ($property->getAttributes(ConstraintInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $fieldConstraints[] = $attribute->newInstance();

                if ($requiredShouldBeAdded && $attribute->getName() === Required::class) {
                    $requiredShouldBeAdded = false;
                }
            }

            if ($requiredShouldBeAdded) {
                if ($fieldConstraints === []) {
                    $fieldConstraints = [new Required()];
                } else {
                    array_unshift($fieldConstraints, new Required());
                }
            }

            if ($fieldConstraints) {
                $fieldsConstraints[$property->name] = $fieldConstraints;
            }
        }

        /** @var RuntimeValidator<T> */
        return new RuntimeValidator($this->validatorRegistry, $fieldsConstraints);
    }
}
