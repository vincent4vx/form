<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Closure;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Quatrevieux\Form\Transformer\AbstractGeneratedFormTransformer;
use Quatrevieux\Form\Transformer\Field\TransformationError;
use Quatrevieux\Form\Transformer\FormTransformerInterface;
use Quatrevieux\Form\Transformer\TransformationResult;
use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Validator\FieldError;

/**
 * Class generator helper for generates {@see FormTransformerInterface} class
 */
final class FormTransformerClass
{
    public readonly PhpFile $file;
    public readonly ClassType $class;
    public readonly Method $fromHttpMethod;
    public readonly Method $toHttpMethod;
    public readonly Method $transformFieldFromHttpMethod;
    public readonly Method $transformFieldToHttpMethod;

    /**
     * @var array<string, string>
     */
    private array $propertyNameToHttpFieldName = [];

    /**
     * @var array<string, list<Closure(string):string>>
     */
    private array $fromHttpFieldsTransformationExpressions = [];

    /**
     * @var array<string, bool>
     */
    private array $fieldTransformersCanThrowError = [];

    /**
     * @var array<string, TransformationError|null>
     */
    private array $fieldTransformersErrorHandling = [];

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

        $this->file->addUse(TransformationResult::class);
        $this->file->addUse(FieldError::class);

        $this->fromHttpMethod = Method::from([FormTransformerInterface::class, 'transformFromHttp'])->setComment(null);
        $this->toHttpMethod = Method::from([FormTransformerInterface::class, 'transformToHttp'])->setComment(null);
        $this->transformFieldFromHttpMethod = Method::from([AbstractGeneratedFormTransformer::class, 'transformFieldFromHttp'])->setComment(null)->setAbstract(false);
        $this->transformFieldToHttpMethod = Method::from([AbstractGeneratedFormTransformer::class, 'transformFieldToHttp'])->setComment(null)->setAbstract(false);

        $this->class->setExtends(AbstractGeneratedFormTransformer::class);
        $this->class->addMember($this->fromHttpMethod);
        $this->class->addMember($this->toHttpMethod);
        $this->class->addMember($this->transformFieldFromHttpMethod);
        $this->class->addMember($this->transformFieldToHttpMethod);
    }

    /**
     * Declare a field existence
     *
     * All fields must be declared to be handled by transformer,
     * otherwise fields without transformers will be ignored.
     *
     * @param string $fieldName Field name to declare
     * @param string $httpFieldName Mapped HTTP field name
     * @param TransformationError|null $errorHandling Error handling configuration
     *
     * @return void
     */
    public function declareField(string $fieldName, string $httpFieldName, ?TransformationError $errorHandling = null): void
    {
        $this->propertyNameToHttpFieldName[$fieldName] = $httpFieldName;
        $this->fromHttpFieldsTransformationExpressions[$fieldName] = [];
        $this->toHttpFieldsTransformationExpressions[$fieldName] = [];
        $this->fieldTransformersErrorHandling[$fieldName] = $errorHandling;
    }

    /**
     * Add new field transformer expressions for both from and to http
     *
     * @param string $fieldName DTO property name
     * @param Closure(string):string $fromHttpExpression Generator of transformFromHttp expression. Takes as parameter the previous expression or HTTP field value
     * @param Closure(string):string $toHttpExpression Generator of transformToHttp expression. Takes as parameter the previous expression or DTO property value
     * @param bool $canThrowError Whether the transformer can throw an error. See {@see FormTransformerInterface::transformFromHttp()}.
     *
     * @return void
     */
    public function addFieldTransformationExpression(string $fieldName, Closure $fromHttpExpression, Closure $toHttpExpression, bool $canThrowError): void
    {
        $this->fromHttpFieldsTransformationExpressions[$fieldName][] = $fromHttpExpression;
        $this->toHttpFieldsTransformationExpressions[$fieldName][] = $toHttpExpression;

        if (empty($this->fieldTransformersCanThrowError[$fieldName])) {
            $this->fieldTransformersCanThrowError[$fieldName] = $canThrowError;
        }
    }

    /**
     * Generate the {@see FormTransformerInterface::transformFromHttp()} method body
     */
    public function generateFromHttp(): void
    {
        $this->fromHttpMethod->addBody('$errors = [];');
        $this->fromHttpMethod->addBody('$transformed = ' . $this->generateInlineFromHttpArray() . ';');
        $this->fromHttpMethod->addBody($this->generateUnsafeFromHttpTransformations());
        $this->fromHttpMethod->addBody('return new TransformationResult($transformed, $errors);');

        $this->transformFieldFromHttpMethod->setBody($this->generateTransformFieldFromHttpBody());
    }

    /**
     * Generate the {@see FormTransformerInterface::transformToHttp()} method body
     */
    public function generateToHttp(): void
    {
        $this->toHttpMethod->addBody($this->generateTransformToHttpBody());
        $this->transformFieldToHttpMethod->addBody($this->generateTransformFieldToHttpBody());
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

    private function generateTransformFieldFromHttpBody(): string
    {
        $cases = '';

        foreach ($this->fromHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            $fieldNameString = Code::value($fieldName);
            $fieldExpression = '$value';

            foreach ($expressions as $expression) {
                $fieldExpression = $expression($fieldExpression);
            }

            $cases .= '    ' . $fieldNameString . ' => ' . $fieldExpression . ',' . PHP_EOL;
        }

        return "return match (\$fieldName) {\n$cases};";
    }

    private function generateTransformToHttpBody(): string
    {
        $arrayItems = '';

        foreach ($this->toHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            $fieldNameString = Code::value($fieldName);
            $httpFieldString = Code::value($this->propertyNameToHttpFieldName[$fieldName] ?? $fieldName);

            $fieldExpression = '$value[' . $fieldNameString . '] ?? null';

            foreach (array_reverse($expressions) as $expression) {
                $fieldExpression = $expression($fieldExpression);
            }

            $arrayItems .= '    ' . $httpFieldString . ' => ' . $fieldExpression . ',' . PHP_EOL;
        }

        return "return [\n$arrayItems];";
    }

    private function generateTransformFieldToHttpBody(): string
    {
        $matches = '';

        foreach ($this->toHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            $fieldNameString = Code::value($fieldName);
            $matchExpression = '$value';

            foreach (array_reverse($expressions) as $expression) {
                $matchExpression = $expression($matchExpression);
            }

            $matches .= '    ' . $fieldNameString . ' => ' . $matchExpression . ',' . PHP_EOL;
        }

        return "return match (\$fieldName) {\n$matches};";
    }

    private function generateInlineFromHttpArray(): string
    {
        $code = '[' . PHP_EOL;
        $unsafeFields = $this->fieldTransformersCanThrowError;

        foreach ($this->fromHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            // Ignore all fields that can throw any error
            if (!empty($unsafeFields[$fieldName])) {
                continue;
            }

            $fieldNameString = Code::value($fieldName);
            $httpFieldString = Code::value($this->propertyNameToHttpFieldName[$fieldName] ?? $fieldName);

            $fieldExpression = '$value[' . $httpFieldString . '] ?? null';

            foreach ($expressions as $expression) {
                $fieldExpression = $expression($fieldExpression);
            }

            $code .= '    ' . $fieldNameString . ' => ' . $fieldExpression . ',' . PHP_EOL;
        }

        return $code . ']';
    }

    private function generateUnsafeFromHttpTransformations(): string
    {
        $code = '';
        $unsafeFields = $this->fieldTransformersCanThrowError;

        foreach ($this->fromHttpFieldsTransformationExpressions as $fieldName => $expressions) {
            // Safe fields are already handled in generateInlineFromHttpArray()
            if (empty($unsafeFields[$fieldName])) {
                continue;
            }

            // Get translator only if there is at least one unsafe field
            if (!$code) {
                $code = '$translator = $this->registry->getTranslator();' . PHP_EOL;
            }

            $errorHandlingConfiguration = $this->fieldTransformersErrorHandling[$fieldName] ?? null;

            $fieldNameString = Code::value($fieldName);
            $httpFieldString = Code::value($this->propertyNameToHttpFieldName[$fieldName] ?? $fieldName);

            $fieldExpression = '$value[' . $httpFieldString . '] ?? null';

            $errorValue = $errorHandlingConfiguration?->keepOriginalValue ? $fieldExpression : 'null';
            $errorMessage = $errorHandlingConfiguration?->message ? Code::value($errorHandlingConfiguration->message) : '$e->getMessage()';
            $errorCode = Code::value($errorHandlingConfiguration?->code ?? TransformationError::CODE);

            foreach ($expressions as $expression) {
                $fieldExpression = $expression($fieldExpression);
            }

            $tryExpression = <<<PHP
            try {
                \$transformed[{$fieldNameString}] = {$fieldExpression};
            }
            PHP;

            if (!$errorHandlingConfiguration?->ignore && !$errorHandlingConfiguration?->hideSubErrors) {
                $tryExpression .= <<<PHP
                 catch (\Quatrevieux\Form\Transformer\TransformerException \$e) {
                    \$errors[{$fieldNameString}] = \$e->errors;
                    \$transformed[{$fieldNameString}] = {$errorValue};
                }
                PHP;
            }

            if ($errorHandlingConfiguration?->ignore) {
                $tryExpression .= <<<PHP
                 catch (\Exception \$e) {
                    \$transformed[{$fieldNameString}] = {$errorValue};
                }
                PHP;
            } else {
                $tryExpression .= <<<PHP
                 catch (\Exception \$e) {
                    \$errors[{$fieldNameString}] = new FieldError({$errorMessage}, [], {$errorCode}, \$translator);
                    \$transformed[{$fieldNameString}] = {$errorValue};
                }
                PHP;
            }

            $code .= PHP_EOL . $tryExpression . PHP_EOL;
        }

        return $code;
    }
}
