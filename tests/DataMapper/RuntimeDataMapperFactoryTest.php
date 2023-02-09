<?php

namespace Quatrevieux\Form\DataMapper;

use ArrayObject;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\FormTestCase;

class RuntimeDataMapperFactoryTest extends FormTestCase
{
    public function test_create_simple()
    {
        $factory = new RuntimeDataMapperFactory();
        $dataMapper = $factory->create(SimpleRequest::class);

        $this->assertEquals(new PublicPropertyDataMapper(SimpleRequest::class), $dataMapper);
        $this->assertSame(SimpleRequest::class, $dataMapper->className());
    }

    public function test_create_with_custom_data_mapper()
    {
        $factory = new RuntimeDataMapperFactory();
        $dataMapper = $factory->create(FormWithCustomDataMapper::class);

        $this->assertEquals(new CustomDataMapper(FormWithCustomDataMapper::class), $dataMapper);
        $this->assertSame(FormWithCustomDataMapper::class, $dataMapper->className());

        $dto = $dataMapper->toDataObject(['foo' => 'bar', 'bar' => 42]);

        $this->assertInstanceOf(FormWithCustomDataMapper::class, $dto);
        $this->assertSame('bar', $dto->foo);
        $this->assertSame(42, $dto->bar);
        $this->assertEquals(['foo' => 'bar', 'bar' => 42], $dto->getArrayCopy());

        $this->assertEquals(['foo' => 'bar', 'bar' => 42], $dataMapper->toArray($dto));
    }
}

#[InstantiateWith(CustomDataMapper::class)]
class FormWithCustomDataMapper extends ArrayObject
{
    public ?string $foo;
    public ?int $bar;
}

class CustomDataMapper implements DataMapperInterface
{
    public function __construct(
        private readonly string $className,
    ) {
    }

    public function toDataObject(array $fields): object
    {
        $class = $this->className;

        return new $class($fields, ArrayObject::ARRAY_AS_PROPS | ArrayObject::STD_PROP_LIST);
    }

    public function toArray(object $data): array
    {
        return $data->getArrayCopy();
    }

    public function className(): string
    {
        return $this->className;
    }
}
