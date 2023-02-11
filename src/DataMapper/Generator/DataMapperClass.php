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
final class DataMapperClass
{
    public readonly PhpFile $file;
    public readonly ClassType $class;
    public readonly Method $classNameMethod;
    public readonly Method $toDataObjectMethod;
    public readonly Method $toArrayMethod;

    /**
     * @param string $className Class name of the generated DataMapper class
     */
    public function __construct(string $className)
    {
        $this->file = new PhpFile();
        $this->class = $this->file->addClass($className);

        $this->classNameMethod = Method::from([DataMapperInterface::class, 'className'])->setComment(null);
        $this->toDataObjectMethod = Method::from([DataMapperInterface::class, 'toDataObject'])->setComment(null);
        $this->toArrayMethod = Method::from([DataMapperInterface::class, 'toArray'])->setComment(null);

        $this->class->addImplement(DataMapperInterface::class);
        $this->class->addMember($this->classNameMethod);
        $this->class->addMember($this->toDataObjectMethod);
        $this->class->addMember($this->toArrayMethod);
    }

    /**
     * Define the class name of the DTO handled by the generated data mapper
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
     * Set the body of the `toDataObject()` method
     *
     * @param string $code Body of the method
     *
     * @return void
     */
    public function setToDataObjectBody(string $code): void
    {
        $this->toDataObjectMethod->setBody($code);
    }

    /**
     * Set the body of the `toArray()` method
     *
     * @param string $code Body of the method
     *
     * @return void
     */
    public function setToArrayBody(string $code): void
    {
        $this->toArrayMethod->setBody($code);
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
