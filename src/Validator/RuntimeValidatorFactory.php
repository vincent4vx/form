<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\DefaultValue;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
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
        private readonly RegistryInterface $registry,
    ) {}

    /**
     * {@inheritdoc}
     *
     * @param class-string<T> $dataClass
     * @template T as object
     * @return ValidatorInterface<T>
     */
    public function create(string $dataClass): ValidatorInterface
    {
        $reflectionClass = new ReflectionClass($dataClass);
        $fieldsConstraints = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $requiredShouldBeAdded = ($type = $property->getType()) && !$type->allowsNull() && !$property->hasDefaultValue();
            $fieldConstraints = [];

            foreach ($property->getAttributes(ConstraintInterface::class, ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
                $fieldConstraints[] = $attribute->newInstance();

                if ($requiredShouldBeAdded && $attribute->getName() === Required::class) {
                    $requiredShouldBeAdded = false;
                }
            }

            // If a default value is defined, we assume that the field is not required
            // This behavior can be overridden by defining the Required attribute explicitly
            // See: https://github.com/vincent4vx/form/issues/15
            if ($requiredShouldBeAdded) {
                $requiredShouldBeAdded = $property->getAttributes(DefaultValue::class) === [];
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
        return new RuntimeValidator($this->registry, $fieldsConstraints);
    }
}
