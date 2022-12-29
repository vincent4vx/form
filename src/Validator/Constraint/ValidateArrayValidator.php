<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

use function array_map;
use function implode;
use function is_array;
use function strtr;

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
        private ConstraintValidatorRegistryInterface $registry
    ) {
    }

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
    public function generate(ConstraintInterface $constraint, string $fieldAccessor, ValidatorGenerator $generator): string
    {
        $varName = Code::varName($fieldAccessor);
        $itemErrorExpression = $this->generateItemErrorExpression($constraint);
        $fieldErrorExpression = $this->generateFieldErrorExpression($constraint);

        $constraints = array_map(
            fn (ConstraintInterface $constraint) => $generator->validator($constraint, '$item'),
            $constraint->constraints
        );
        $constraints = implode(' ?? ', $constraints);

        $initErrorsExpression = $constraint->aggregateErrors ? '$valid = true; $errors = \'\';' : '$valid = true; $errors = [];';
        $expression = "if (\$error = $constraints) { \$valid = false; {$itemErrorExpression} }";
        $expression = "foreach (\$value as \$key => \$item) { $expression }";

        return "!\is_array({$varName} = {$fieldAccessor}) ? null : (function (\$value) use(\$data, \$translator) { {$initErrorsExpression} {$expression} return \$valid ? null : {$fieldErrorExpression}; })({$varName})";
    }

    private function generateFieldErrorExpression(ValidateArray $constraint): string
    {
        if (!$constraint->aggregateErrors) {
            return '$errors';
        }

        return Code::new('FieldError', [
            $constraint->message,
            ['item_errors' => Code::raw('$errors')],
            ValidateArray::CODE
        ]);
    }

    private function generateItemErrorExpression(ValidateArray $constraint): string
    {
        if ($constraint->aggregateErrors) {
            return '$errors .= ' . Call::object('$translator')->trans($constraint->itemMessage, [
                '{{ key }}' => Code::raw('$key'),
                '{{ error }}' => Code::raw('(\is_array($error) ? \'\' : $error->withTranslator($translator))'),
            ]) . ';';
        }

        // @todo skip is_array check
        return '$errors[$key] = \is_array($error) ? $error : $error->withTranslator($translator);';
    }
}
