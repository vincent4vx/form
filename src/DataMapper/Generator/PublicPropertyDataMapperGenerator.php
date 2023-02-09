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
 * Generate data mapper with same behavior as {@see PublicPropertyDataMapper}
 *
 * @implements DataMapperTypeGeneratorInterface<PublicPropertyDataMapper>
 */
final class PublicPropertyDataMapperGenerator implements DataMapperTypeGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function supports(DataMapperInterface $dataMapper): bool
    {
        return $dataMapper instanceof PublicPropertyDataMapper;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(DataMapperInterface $dataMapper, DataMapperClass $class): void
    {
        $class->setClassName($dataMapper->className());

        $class->addToDataObjectBody('$object = new ?();', [new Literal($dataMapper->className())]);

        $classReflection = new ReflectionClass($dataMapper->className());

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
