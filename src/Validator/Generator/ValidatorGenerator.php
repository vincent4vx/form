<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
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
                $generator = $constraint->getValidator($this->validatorRegistry);

                if (!$generator instanceof ConstraintValidatorGeneratorInterface) {
                    $generator = $this->genericValidatorGenerator;
                }

                $classHelper->addConstraintCode($field, $generator->generate($constraint, '($data->' . $field . ' ?? null)'));
            }
        }

        $classHelper->generate();

        return $classHelper->code();
    }
}
