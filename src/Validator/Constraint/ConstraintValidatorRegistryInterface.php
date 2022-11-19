<?php

namespace Quatrevieux\Form\Validator\Constraint;

/**
 * Registry of validator instances
 */
interface ConstraintValidatorRegistryInterface
{
    /**
     * Get a validator instance
     *
     * @param class-string<V> $className Validator class name
     *
     * @return V
     * @template V as ConstraintValidatorInterface
     *
     * @see ConstraintInterface::getValidator()
     */
    public function getValidator(string $className): ConstraintValidatorInterface;
}
