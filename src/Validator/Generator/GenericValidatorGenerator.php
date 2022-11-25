<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\ValidatorInterface;

/**
 * Validator generator used by default, when there is no available generator for the given constraint
 * This generator will simply inline constraint instantiation, and call `getValidator()->validate(...)`
 *
 * Generated code example:
 * '($__constraint_14ab54f6d = new MyConstraint(foo: "bar"))->getValidator($this->validatorRegistry)->validate($__constraint_14ab54f6d, ($data->foo ?? null))'
 */
final class GenericValidatorGenerator implements ConstraintValidatorGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, string $fieldAccessor): string
    {
        $newConstraintExpression = Code::newExpression($constraint);
        $constraintVarName = Code::varName($newConstraintExpression, 'constraint');

        // Optimisation of SelfValidatedConstraint
        if ($constraint instanceof ValidatorInterface) {
            return "($constraintVarName = $newConstraintExpression)->validate($constraintVarName, $fieldAccessor)";
        } else {
            return "($constraintVarName = $newConstraintExpression)->getValidator(\$this->validatorRegistry)->validate($constraintVarName, $fieldAccessor)";
        }
    }
}
