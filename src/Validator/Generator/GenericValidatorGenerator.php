<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;

/**
 * Validator generator used by default, when there is no available generator for the given constraint
 * This generator will simply inline constraint instantiation, and call `getValidator()->validate(...)`
 *
 * Generated code example:
 * '($__constraint_14ab54f6d = new MyConstraint(foo: "bar"))->getValidator($this->registry)->validate($__constraint_14ab54f6d, ($data->foo ?? null), $data)'
 *
 * @implements ConstraintValidatorGeneratorInterface<ConstraintInterface>
 */
final class GenericValidatorGenerator implements ConstraintValidatorGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $newConstraintExpression = Code::instantiate($constraint);
        $constraintVarName = Code::varName($newConstraintExpression, 'constraint');

        // Optimisation of SelfValidatedConstraint
        if ($constraint instanceof ConstraintValidatorInterface) {
            return FieldErrorExpression::undefined(fn(string $fieldAccessor) => "($constraintVarName = $newConstraintExpression)->validate($constraintVarName, $fieldAccessor, \$data)");
        } else {
            return FieldErrorExpression::undefined(fn(string $fieldAccessor) => "($constraintVarName = $newConstraintExpression)->getValidator(\$this->registry)->validate($constraintVarName, $fieldAccessor, \$data)");
        }
    }
}
