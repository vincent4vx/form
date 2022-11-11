<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\RuntimeValidator;

final class ValidatorGenerator
{
    public function __construct(
        private readonly ConstraintValidatorRegistryInterface $validatorRegistry,
    ) {

    }

    public function generate(string $className, RuntimeValidator $validator, GeneratedValidatorFactory $factory): string
    {
        $classHelper = new ValidatorClass($className);

        foreach ($validator->getFieldsConstraints() as $field => $constraints) {
            foreach ($constraints as $constraint) {
                $generator = $constraint->getValidator($this->validatorRegistry);

                if (!$generator instanceof ConstraintValidatorGeneratorInterface) {
                    $generator = new GenericValidatorGenerator(); // @todo keep instance
                }

                $classHelper->addConstraintCode($field, $generator->generate($constraint, '($data->' . $field . ' ?? null)'));
            }
        }

        $classHelper->generate();

        return $classHelper->code();
    }
}
