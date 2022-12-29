<?php

namespace Quatrevieux\Form\Validator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
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
     * @var array<string, list<string>>
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
        $constructor->addPromotedParameter('validatorRegistry')
            ->setPrivate()
            ->setReadOnly()
            ->setType(ConstraintValidatorRegistryInterface::class)
        ;
    }

    /**
     * Add a new constraint validator expression on the given field
     * All constraints will be applied successively, on validation will be stopped on first error
     *
     * @param string $fieldName Name of the field to validate
     * @param string $errorExpression Validation expression in PHP. This expression must return a FieldError object on error, or null on success
     *
     * @todo use closure instead of string for expression
     */
    public function addConstraintCode(string $fieldName, string $errorExpression): void
    {
        $this->fieldsConstraintsExpressions[$fieldName][] = $errorExpression;
    }

    /**
     * Generates the body method of {@see ValidatorInterface::validate()}
     */
    public function generate(): void
    {
        // @todo optimize empty validator
        // @todo optimize to skip is_array check
        $this->validateMethod->addBody('$errors = $previousErrors;');
        $this->validateMethod->addBody('$translator = $this->validatorRegistry->getTranslator();');

        foreach ($this->fieldsConstraintsExpressions as $fieldName => $expressions) {
            $expressions = array_map(fn (string $expression) => "($expression)", $expressions);
            $expressions = implode(' ?? ', $expressions);
            $fieldNameString = Code::value($fieldName);

            $this->validateMethod->addBody(
                <<<PHP
            if (!isset(\$previousErrors[{$fieldNameString}]) && \$__error_{$fieldName} = {$expressions}) {
                \$errors[{$fieldNameString}] = is_array(\$__error_{$fieldName}) ? \$__error_{$fieldName} : \$__error_{$fieldName}->withTranslator(\$translator);
            }

            PHP
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
