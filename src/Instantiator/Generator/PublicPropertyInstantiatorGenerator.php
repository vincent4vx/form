<?php

namespace Quatrevieux\Form\Instantiator\Generator;

use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\PhpGenerator\Method;
use ReflectionClass;
use ReflectionProperty;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Instantiator\PublicPropertyInstantiator;

/**
 * @implements InstantiatorTypeGeneratorInterface<PublicPropertyInstantiator>
 */
final class PublicPropertyInstantiatorGenerator implements InstantiatorTypeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(InstantiatorInterface $instantiator): bool
    {
        return $instantiator instanceof PublicPropertyInstantiator;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(InstantiatorInterface $instantiator, InstantiatorClass $class): void
    {
        $class->setClassName($instantiator->className());

        $class->addInstantiateBody('$object = new ?();', [new Literal($instantiator->className())]);

        $classReflection = new ReflectionClass($instantiator->className());

        foreach ($classReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->getType() || $property->getType()->allowsNull()) { // @todo in case of default value ?
                $class->addInstantiateBody('$object->? = $fields[?] \?\? null;', [$property->name, $property->name]);
            } else {
                $tmpVarname = '___tmp' . bin2hex(random_bytes(8));

                $class->addInstantiateBody(<<<'PHP'
                    if (($? = $fields[?] \?\? null) !== null) {
                        $object->? = $?;
                    }
                    PHP
                    , [$tmpVarname, $property->name, $property->name, $tmpVarname]
                );
            }
        }

        $class->addInstantiateBody('return $object;');
    }
}
