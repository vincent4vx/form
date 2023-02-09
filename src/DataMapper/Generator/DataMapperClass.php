<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Quatrevieux\Form\DataMapper\DataMapperInterface;

/**
 * Class generator helper for generates {@see DataMapperInterface} class
 */
final class InstantiatorClass
{
    public readonly PhpFile $file;
    public readonly ClassType $class;
    public readonly Method $classNameMethod;
    public readonly Method $instantiateMethod;
    public readonly Method $exportMethod;

    /**
     * @param string $className Class name of the generated Instantiator class
     */
    public function __construct(string $className)
    {
        $this->file = new PhpFile();
        $this->class = $this->file->addClass($className);

        $this->classNameMethod = Method::from([DataMapperInterface::class, 'className'])->setComment(null);
        $this->instantiateMethod = Method::from([DataMapperInterface::class, 'toDataObject'])->setComment(null);
        $this->exportMethod = Method::from([DataMapperInterface::class, 'toArray'])->setComment(null);

        $this->class->addImplement(DataMapperInterface::class);
        $this->class->addMember($this->classNameMethod);
        $this->class->addMember($this->instantiateMethod);
        $this->class->addMember($this->exportMethod);
    }

    /**
     * Define the class name of the DTO handled by the generated instantiator
     * Will generate method :
     *
     * public function className(): string
     * {
     *     return MyDtoClassName::class;
     * }
     *
     * @param class-string $className DTO FQCN
     * @return void
     */
    public function setClassName(string $className): void
    {
        $this->classNameMethod->setBody('return ?::class;', [new Literal($className)]);
    }

    /**
     * Add code to the `instantiate()` method body
     *
     * @param string $code Line of code to add. Use ? as placeholder.
     * @param list<mixed>|null $args Placeholder parameters. Use `new Litteral()` to ignore autoformatting strings.
     *
     * @return void
     *
     * @see Method::addBody() Method call internally
     */
    public function addInstantiateBody(string $code, ?array $args = null): void
    {
        $this->instantiateMethod->addBody($code, $args);
    }

    /**
     * Add code to the `export()` method body
     *
     * @param string $code Line of code to add. Use ? as placeholder.
     * @param list<mixed>|null $args Placeholder parameters. Use `new Litteral()` to ignore autoformatting strings.
     *
     * @return void
     *
     * @see Method::addBody() Method call internally
     */
    public function addExportBody(string $code, ?array $args = null): void
    {
        $this->exportMethod->addBody($code, $args);
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
