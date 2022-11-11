<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\Constraint\Required;
use ReflectionClass;
use ReflectionProperty;

class RuntimeValidatorFactory implements ValidatorFactoryInterface
{
    public function __construct(
        private readonly ConstraintValidatorRegistryInterface $validatorRegistry
    ) {
    }

    /**
     * @inheritDoc
     * @todo null validator for optimisations
     */
    public function create(string $dataClass): ValidatorInterface
    {
        $reflectionClass = new ReflectionClass($dataClass);
        $fieldsConstraints = [];

        foreach ($reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $requiredShouldBeAdded = !$property->getType()->allowsNull();
            $fieldConstraints = [];

            foreach ($property->getAttributes(ConstraintInterface::class, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
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

        return new RuntimeValidator($this->validatorRegistry, $fieldsConstraints);
    }
}
