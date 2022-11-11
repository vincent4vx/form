<?php

namespace Quatrevieux\Form\Validator\Constraint;

/**
 * Base type for define a constraint
 * The implementation must be declared as attribute using `#[Attribute(Attribute::TARGET_PROPERTY)]`
 */
interface ConstraintInterface
{
    /**
     * Get the related constraint validator
     * Can be $this in case of {@see SelfValidatedConstraint}
     *
     * @param ConstraintValidatorRegistryInterface $registry
     *
     * @return ConstraintValidatorInterface<static>
     */
    public function getValidator(ConstraintValidatorRegistryInterface $registry): ConstraintValidatorInterface;
}
