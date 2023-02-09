<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use ReflectionClass;
use ReflectionProperty;
use Quatrevieux\Form\DataMapper\DataMapperInterface;
use Quatrevieux\Form\DataMapper\PublicPropertyDataMapper;

/**
 * @implements InstantiatorTypeGeneratorInterface<PublicPropertyDataMapper>
 */
final class PublicPropertyInstantiatorGenerator implements InstantiatorTypeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(DataMapperInterface $instantiator): bool
    {
        return $instantiator instanceof PublicPropertyDataMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(DataMapperInterface $instantiator, DataMapperClass $class): void
    {
        $class->setClassName($instantiator->className());

        $class->addToDataObjectBody('$object = new ?();', [new Literal($instantiator->className())]);

        $classReflection = new ReflectionClass($instantiator->className());

        foreach ($classReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->getType() || $property->getType()->allowsNull()) {
                $class->addToDataObjectBody('$object->? = $fields[?] \?\? null;', [$property->name, $property->name]);
            } else {
                $tmpVarname = '___tmp' . bin2hex(random_bytes(8));

                $class->addToDataObjectBody(
                    <<<'PHP'
                    if (($? = $fields[?] \?\? null) !== null) {
                        $object->? = $?;
                    }
                    PHP
                    ,
                    [$tmpVarname, $property->name, $property->name, $tmpVarname]
                );
            }
        }

        $class->addToDataObjectBody('return $object;');
        $class->addToArrayBody('return get_object_vars($data);');
    }
}
