<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpression;
use Quatrevieux\Form\Validator\Generator\FieldErrorExpressionInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

use function implode;
use function is_array;

/**
 * Validator for {@see ValidateArray}
 * This class must be instantiated by the {@see ValidateArray::getValidator()} method for allowing the injection of validator registry
 *
 * @internal
 * @implements ConstraintValidatorInterface<ValidateArray>
 * @implements ConstraintValidatorGeneratorInterface<ValidateArray>
 */
final class ValidateArrayValidator implements ConstraintValidatorInterface, ConstraintValidatorGeneratorInterface
{
    public function __construct(
        private RegistryInterface $registry,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): FieldError|array|null
    {
        if (!is_array($value)) {
            return null;
        }

        $errors = [];
        $constraints = $constraint->constraints;
        $registry = $this->registry;
        $translator = $registry->getTranslator();

        foreach ($value as $key => $item) {
            foreach ($constraints as $itemConstraint) {
                $error = $itemConstraint->getValidator($registry)->validate($itemConstraint, $item, $data);

                if ($error !== null) {
                    $errors[$key] = is_array($error) ? $error : $error->withTranslator($translator);
                    break;
                }
            }
        }

        if (!$errors) {
            return null;
        }

        if (!$constraint->aggregateErrors) {
            return $errors;
        }

        $message = $constraint->message;
        $itemMessage = $constraint->itemMessage;
        $itemErrors = '';

        foreach ($errors as $key => $error) {
            $itemErrors .= $translator->trans($itemMessage, [
                '{{ key }}' => (string) $key,
                '{{ error }}' => is_array($error) ? '' : (string) $error,
            ]);
        }

        return new FieldError($message, ['item_errors' => $itemErrors], ValidateArray::CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ConstraintInterface $constraint, ValidatorGenerator $generator): FieldErrorExpressionInterface
    {
        $constraints = [];
        $returnType = 0;

        foreach ($constraint->constraints as $itemConstraint) {
            $expression = $generator->validator($itemConstraint);
            $constraints[] = $expression->generate('$item');
            $returnType |= $expression->returnType();
        }

        $fieldErrorExpression = $this->generateFieldErrorExpression($constraint);
        $constraints = implode(' ?? ', $constraints);
        $itemErrorExpression = $this->generateItemErrorExpression($constraint, $returnType);

        $initErrorsExpression = $constraint->aggregateErrors ? '$valid = true; $errors = \'\';' : '$valid = true; $errors = [];';
        $expression = "if (\$error = $constraints) { \$valid = false; {$itemErrorExpression} }";
        $expression = "foreach (\$value as \$key => \$item) { $expression }";

        return new FieldErrorExpression(
            function (string $fieldAccessor) use ($fieldErrorExpression, $expression, $initErrorsExpression) {
                $varName = Code::varName($fieldAccessor);
                return "!\is_array({$varName} = {$fieldAccessor}) ? null : (function (\$value) use(\$data, \$translator) { {$initErrorsExpression} {$expression} return \$valid ? null : {$fieldErrorExpression}; })({$varName})";
            },
            $constraint->aggregateErrors ? FieldErrorExpression::RETURN_TYPE_SINGLE : FieldErrorExpression::RETURN_TYPE_AGGREGATE,
        );
    }

    private function generateFieldErrorExpression(ValidateArray $constraint): string
    {
        if (!$constraint->aggregateErrors) {
            return '$errors';
        }

        return Code::new('FieldError', [
            $constraint->message,
            ['item_errors' => Code::raw('$errors')],
            ValidateArray::CODE,
        ]);
    }

    private function generateItemErrorExpression(ValidateArray $constraint, int $returnType): string
    {
        if ($constraint->aggregateErrors) {
            $errorExpression = match ($returnType) {
                FieldErrorExpressionInterface::RETURN_TYPE_SINGLE => '$error->withTranslator($translator)',
                FieldErrorExpressionInterface::RETURN_TYPE_AGGREGATE => "''",
                default => '(\is_array($error) ? \'\' : $error->withTranslator($translator))',
            };

            return '$errors .= ' . Call::object('$translator')->trans($constraint->itemMessage, [
                '{{ key }}' => Code::raw('$key'),
                '{{ error }}' => Code::raw($errorExpression),
            ]) . ';';
        }

        return match ($returnType) {
            FieldErrorExpressionInterface::RETURN_TYPE_SINGLE => '$errors[$key] = $error->withTranslator($translator);',
            FieldErrorExpressionInterface::RETURN_TYPE_AGGREGATE => '$errors[$key] = $error;',
            default => '$errors[$key] = \is_array($error) ? $error : $error->withTranslator($translator);',
        };
    }
}
