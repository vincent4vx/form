<?php

namespace Quatrevieux\Form\View\Generator;

use Closure;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\PhpExpressionInterface;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\FormViewInstantiatorInterface;

/**
 * Class generator helper for generates {@see FormViewInstantiatorInterface} class
 */
final class FormViewInstantiatorClass
{
    public readonly PhpFile $file;
    public readonly ClassType $class;
    public readonly Method $submittedMethod;
    public readonly Method $defaultMethod;

    /**
     * @var array<array-key, Closure(string, string, ?string):string>
     */
    private array $fieldViewExpressions = [];

    /**
     * @var array<array-key, string>
     */
    private array $propertyNameToHttpFieldName = [];

    /**
     * @param string $className Class name of the generated FormViewInstantiator class
     */
    public function __construct(string $className)
    {
        $this->file = new PhpFile();
        $this->class = $this->file->addClass($className);

        $this->submittedMethod = Method::from([FormViewInstantiatorInterface::class, 'submitted'])->setComment(null);
        $this->defaultMethod = Method::from([FormViewInstantiatorInterface::class, 'default'])->setComment(null);

        $this->class->addImplement(FormViewInstantiatorInterface::class);
        $this->class->addMember($this->submittedMethod);
        $this->class->addMember($this->defaultMethod);

        $this->class->addMethod('__construct')
            ->addPromotedParameter('registry')
            ->setPrivate()
            ->setType(RegistryInterface::class)
        ;
    }

    /**
     * Define a field view instantiation expression
     *
     * @param int|string $field The field name. Can be an int in case of an array field
     * @param string $httpField The HTTP field name
     * @param Closure(string, string, ?string):string $fieldViewExpression Expression generator closure. Takes as parameters value accessor, error accessor and the root field name, and returns the expression to generate the field view
     *
     * @return void
     */
    public function declareFieldView(int|string $field, string $httpField, Closure $fieldViewExpression): void
    {
        $this->fieldViewExpressions[$field] = $fieldViewExpression;
        $this->propertyNameToHttpFieldName[$field] = $httpField;
    }

    /**
     * Generate the body of {@see FormViewInstantiatorInterface::submitted()} method
     */
    public function generateSubmitted(): void
    {
        $newExprWithRootField = Code::new(FormView::class, [
            $this->generateFieldsCode('$value', '$errors', '$rootField'),
            Code::raw('$value')
        ]);

        $newExprWithoutRootField = Code::new(FormView::class, [
            $this->generateFieldsCode('$value', '$errors', null),
            Code::raw('$value')
        ]);

        $this->submittedMethod->setBody("return \$rootField === null ? {$newExprWithoutRootField} :  {$newExprWithRootField};");
    }

    /**
     * Generate the body of {@see FormViewInstantiatorInterface::default()} method
     */
    public function generateDefault(): void
    {
        $newExprWithRootField = Code::new(FormView::class, [
            $this->generateFieldsCode(null, null, '$rootField'),
            []
        ]);

        $newExprWithoutRootField = Code::new(FormView::class, [
            $this->generateFieldsCode(null, null, null),
            []
        ]);

        $this->defaultMethod->setBody("return \$rootField === null ? {$newExprWithoutRootField} :  {$newExprWithRootField};");
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

    /**
     * Generate the code to instantiate all fields
     *
     * @param string|null $valueVarName Variable name of the array of HTTP values
     * @param string|null $errorVarName Variable name of the array of FieldError
     * @param string|null $rootFieldVarName Variable name of the root field name
     *
     * @return PhpExpressionInterface[]
     */
    private function generateFieldsCode(?string $valueVarName, ?string $errorVarName, ?string $rootFieldVarName): array
    {
        $fields = [];

        foreach ($this->fieldViewExpressions as $field => $fieldViewExpression) {
            $httpFieldName = $this->propertyNameToHttpFieldName[$field] ?? $field;

            $fields[$field] = Code::raw($fieldViewExpression(
                $valueVarName ? $valueVarName . '[' . Code::value($httpFieldName) . '] ?? null' : 'null',
                $errorVarName ? $errorVarName . '[' . Code::value($field) . '] ?? null' : 'null',
                $rootFieldVarName
            ));
        }

        return $fields;
    }
}
