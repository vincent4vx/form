<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\RuntimeValidator;
use Quatrevieux\Form\Validator\ValidatorInterface;

/**
 * Generator of {@see ValidatorInterface} implementation
 */
final class ValidatorGenerator
{
    public function __construct(
        private readonly RegistryInterface $registry,
        /**
         * @var ConstraintValidatorGeneratorInterface<ConstraintInterface>
         */
        private readonly ConstraintValidatorGeneratorInterface $genericValidatorGenerator = new GenericValidatorGenerator(),
    ) {}

    /**
     * Generates the class implementation of {@see ValidatorInterface} following constrains stored into given validator
     *
     * @param string $className Class name of the validator class to generate
     * @param RuntimeValidator<object> $validator Validator containing constraints to generate
     *
     * @return string PHP file code
     */
    public function generate(string $className, RuntimeValidator $validator): string
    {
        $classHelper = new ValidatorClass($className);

        foreach ($validator->fieldsConstraints as $field => $constraints) {
            foreach ($constraints as $constraint) {
                $classHelper->addConstraintCode($field, $this->validator($constraint));
            }
        }

        $classHelper->generate();

        return $classHelper->code();
    }

    /**
     * Generate PHP validation expression for given constraint
     *
     * @param ConstraintInterface $constraint Constraint instance
     *
     * @return FieldErrorExpressionInterface PHP expression
     */
    public function validator(ConstraintInterface $constraint): FieldErrorExpressionInterface
    {
        $generator = $constraint->getValidator($this->registry);

        if (!$generator instanceof ConstraintValidatorGeneratorInterface) {
            $generator = $this->genericValidatorGenerator;
        }

        return $generator->generate($constraint, $this);
    }
}
