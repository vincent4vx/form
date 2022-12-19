<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\RuntimeValidator;
use Quatrevieux\Form\Validator\ValidatorInterface;

/**
 * Generator of {@see ValidatorInterface} implementation
 */
final class ValidatorGenerator
{
    public function __construct(
        private readonly ConstraintValidatorRegistryInterface $validatorRegistry,
        private readonly ConstraintValidatorGeneratorInterface $genericValidatorGenerator = new GenericValidatorGenerator(),
    ) {
    }

    /**
     * Generates the class implementation of {@see ValidatorInterface} following constrains stored into given validator
     *
     * @param string $className Class name of the validator class to generate
     * @param RuntimeValidator $validator Validator containing constraints to generate
     *
     * @return string PHP file code
     */
    public function generate(string $className, RuntimeValidator $validator): string
    {
        $classHelper = new ValidatorClass($className);

        foreach ($validator->getFieldsConstraints() as $field => $constraints) {
            foreach ($constraints as $constraint) {
                $classHelper->addConstraintCode($field, $this->validator($constraint, '($data->' . $field . ' ?? null)'));
            }
        }

        $classHelper->generate();

        return $classHelper->code();
    }

    /**
     * Generate PHP validation expression for given constraint
     *
     * @param ConstraintInterface $constraint Constraint instance
     * @param string $fieldAccessor Accessor expression to the field value. Ex: '($data->foo ?? null)'
     *
     * @return string PHP expression
     */
    public function validator(ConstraintInterface $constraint, string $fieldAccessor): string
    {
        $generator = $constraint->getValidator($this->validatorRegistry);

        if (!$generator instanceof ConstraintValidatorGeneratorInterface) {
            $generator = $this->genericValidatorGenerator;
        }

        return $generator->generate($constraint, $fieldAccessor, $this);
    }
}
