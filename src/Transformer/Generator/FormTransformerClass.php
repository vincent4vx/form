<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Closure;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\FormTransformerInterface;

/**
 * Class generator helper for generates {@see FormTransformerInterface} class
 */
final class FormTransformerClass
{
    public readonly PhpFile $file;
    public readonly ClassType $class;
    public readonly Method $fromHttpMethod;
    public readonly Method $toHttpMethod;

    /**
     * @var array<string, list<Closure(string):string>>
     */
    private array $fromHttpFieldsTransformationExpressions = [];

    /**
     * @var array<string, list<Closure(string):string>>
     */
    private array $toHttpFieldsTransformationExpressions = [];

    /**
     * @param string $className Class name of the generated FormTransformer class
     */
    public function __construct(string $className)
    {
        $this->file = new PhpFile();
        $this->class = $this->file->addClass($className);

        $this->fromHttpMethod = Method::from([FormTransformerInterface::class, 'transformFromHttp']);
        $this->toHttpMethod = Method::from([FormTransformerInterface::class, 'transformToHttp']);

        $this->class->addImplement(FormTransformerInterface::class);
        $this->class->addMember($this->fromHttpMethod);
        $this->class->addMember($this->toHttpMethod);

        $this->class->addMethod('__construct')
            ->addPromotedParameter('registry')
            ->setPrivate()
            ->setType(FieldTransformerRegistryInterface::class)
        ;
    }

    /**
     * Add new field transformer expressions for both from and to http
     *
     * @param string $fieldName DTO property name
     * @param Closure(string):string $fromHttpExpression Generator of transformFromHttp expression. Takes as parameter the previous expression or HTTP field value
     * @param Closure(string):string $toHttpExpression Generator of transformToHttp expression. Takes as parameter the previous expression or DTO property value
     *
     * @return void
     */
    public function addFieldTransformationExpression(string $fieldName, Closure $fromHttpExpression, Closure $toHttpExpression): void
    {
        $this->fromHttpFieldsTransformationExpressions[$fieldName][] = $fromHttpExpression;
        $this->toHttpFieldsTransformationExpressions[$fieldName][] = $toHttpExpression;
    }

    /**
     * Generate the {@see FormTransformerInterface::transformFromHttp()} method body
     */
    public function generateFromHttp(): void
    {
        $code = 'return [' . PHP_EOL;

        foreach ($this->fromHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            $fieldNameString = var_export($fieldName, true);
            $fieldExpression = '$value[' . $fieldNameString . '] ?? null';

            foreach ($expressions as $expression) {
                $fieldExpression = $expression($fieldExpression);
            }

            $code .= '    ' . $fieldNameString . ' => ' . $fieldExpression . ',' . PHP_EOL;
        }

        $code .= '];';

        $this->fromHttpMethod->addBody($code);
    }

    /**
     * Generate the {@see FormTransformerInterface::transformToHttp()} method body
     */
    public function generateToHttp(): void
    {
        $code = 'return [' . PHP_EOL;

        foreach ($this->toHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            $fieldNameString = var_export($fieldName, true);
            $fieldExpression = '$value[' . $fieldNameString . '] ?? null';

            foreach (array_reverse($expressions) as $expression) {
                $fieldExpression = $expression($fieldExpression);
            }

            $code .= '    ' . $fieldNameString . ' => ' . $fieldExpression . ',' . PHP_EOL;
        }

        $code .= '];';

        $this->toHttpMethod->addBody($code);
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
