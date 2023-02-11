<?php

namespace Quatrevieux\Form\DataMapper;

use Nette\PhpGenerator\Literal;
use Quatrevieux\Form\DataMapper\Generator\DataMapperTypeGeneratorInterface;
use Quatrevieux\Form\Util\Code;
use ReflectionClass;
use ReflectionProperty;
use TypeError;

use function get_object_vars;

/**
 * Simple data mapper implementation using default constructor and fill directly public properties
 *
 * @template T as object
 * @implements DataMapperInterface<T>
 * @implements DataMapperTypeGeneratorInterface<PublicPropertyDataMapper<T>>
 */
final class PublicPropertyDataMapper implements DataMapperInterface, DataMapperTypeGeneratorInterface
{
    public function __construct(
        /**
         * Data transfer object class name
         *
         * @var class-string<T> $className
         */
        private readonly string $className,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function className(): string
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function toDataObject(array $fields): object
    {
        $className = $this->className;
        $object = new $className();

        foreach ($fields as $name => $value) {
            try {
                $object->$name = $value;
            } catch (TypeError $e) {
                // Ignore type error : can occur when trying to set null on a non-nullable property
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(object $data): array
    {
        return get_object_vars($data);
    }

    /**
     * {@inheritdoc}
     */
    public function generateToDataObject(DataMapperInterface $dataMapper): string
    {
        $code = '$object = ' . Code::new($dataMapper->className()) . ';' . PHP_EOL;

        $classReflection = new ReflectionClass($dataMapper->className());

        foreach ($classReflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->name;
            $propertyNameString = Code::value($propertyName);

            if (!$property->getType() || $property->getType()->allowsNull()) {
                $code .= sprintf('$object->%s = $fields[%s] ?? null;', $propertyName, $propertyNameString) . PHP_EOL;
            } else {
                $tmpVarname = new Literal(Code::varName($property->name));

                $code .= <<<PHP
                    if (({$tmpVarname} = \$fields[{$propertyNameString}] ?? null) !== null) {
                        \$object->{$propertyName} = {$tmpVarname};
                    }

                    PHP
                ;
            }
        }

        $code .= 'return $object;';

        return $code;
    }

    /**
     * {@inheritdoc}
     */
    public function generateToArray(DataMapperInterface $dataMapper): string
    {
        return 'return get_object_vars($data);';
    }
}
