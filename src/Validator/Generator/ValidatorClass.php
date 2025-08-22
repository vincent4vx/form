<?php

namespace Quatrevieux\Form\Validator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\ValidatorInterface;

/**
 * Class generator helper for generates {@see ValidatorInterface} class
 */
final class ValidatorClass
{
    public readonly PhpFile $file;
    public readonly ClassType $class;
    public readonly Method $validateMethod;

    /**
     * @var array<string, list<FieldErrorExpressionInterface>>
     */
    private array $fieldsConstraintsExpressions = [];

    /**
     * @param string $className Class name of the generated Validator class
     */
    public function __construct(string $className)
    {
        $this->file = new PhpFile();
        $this->class = $this->file->addClass($className);

        $this->validateMethod = Method::from([ValidatorInterface::class, 'validate'])->setComment(null);

        $this->class->addImplement(ValidatorInterface::class);
        $this->class->addMember($this->validateMethod);
        $this->file->addUse(FieldError::class);

        $constructor = $this->class->addMethod('__construct');
        $constructor->addPromotedParameter('registry')
            ->setPrivate()
            ->setReadOnly()
            ->setType(RegistryInterface::class)
        ;
    }

    /**
     * Add a new constraint validator expression on the given field
     * All constraints will be applied successively, on validation will be stopped on first error
     *
     * @param string $fieldName Name of the field to validate
     * @param FieldErrorExpressionInterface $errorExpression Validation expression in PHP. This expression must return a FieldError object on error, or null on success
     */
    public function addConstraintCode(string $fieldName, FieldErrorExpressionInterface $errorExpression): void
    {
        $this->fieldsConstraintsExpressions[$fieldName][] = $errorExpression;
    }

    /**
     * Generates the body method of {@see ValidatorInterface::validate()}
     */
    public function generate(): void
    {
        if (empty($this->fieldsConstraintsExpressions)) {
            $this->validateMethod->addBody('return $previousErrors;');
            return;
        }

        $this->validateMethod->addBody('$errors = $previousErrors;');
        $this->validateMethod->addBody('$translator = $this->registry->getTranslator();');

        foreach ($this->fieldsConstraintsExpressions as $fieldName => $expressions) {
            $expressionCode = [];
            $returnType = 0;

            foreach ($expressions as $expression) {
                $returnType |= $expression->returnType();
                $expressionCode[] = '(' . $expression->generate('($data->' . $fieldName . ' ?? null)') . ')';
            }

            $expressionCode = implode(' ?? ', $expressionCode);
            $fieldNameString = Code::value($fieldName);
            $errorWithTranslator = match ($returnType) {
                FieldErrorExpressionInterface::RETURN_TYPE_SINGLE => '$__error_' . $fieldName . '->withTranslator($translator)',
                FieldErrorExpressionInterface::RETURN_TYPE_AGGREGATE => '$__error_' . $fieldName,
                default => "is_array(\$__error_{$fieldName}) ? \$__error_{$fieldName} : \$__error_{$fieldName}->withTranslator(\$translator)",
            };

            $this->validateMethod->addBody(
                <<<PHP
            if (!isset(\$previousErrors[{$fieldNameString}]) && \$__error_{$fieldName} = {$expressionCode}) {
                \$errors[{$fieldNameString}] = {$errorWithTranslator};
            }

            PHP,
            );
        }

        $this->validateMethod->addBody('return $errors;');
    }

    /**
     * Dump PHP code of the class
     *
     * @return string
     */
    public function code(): string
    {
        return (new PsrPrinter())->printFile($this->file);
    }
}
