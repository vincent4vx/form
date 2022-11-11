<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;

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
        // @todo optimise for SelfValidatorConstraint
        $newConstraintExpression = 'new \\'.get_class($constraint).'(';

        foreach (get_object_vars($constraint) as $prop => $value) {
            $newConstraintExpression .= $prop . ': ' . var_export($value, true) . ', ';
        }

        $newConstraintExpression .= ')';
        $constraintVarName = '$__constraint_'.md5($newConstraintExpression);

        return "($constraintVarName = $newConstraintExpression)->getValidator(\$this->validatorRegistry)->validate($constraintVarName, $fieldAccessor)";
    }
}
