<?php

namespace Quatrevieux\Form\DataMapper;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use stdClass;

class PublicPropertyDataMapperTest extends TestCase
{
    public function test_empty()
    {
        $mapper = new PublicPropertyDataMapper(stdClass::class);

        $this->assertSame(stdClass::class, $mapper->className());
        $this->assertEquals(new stdClass(), $mapper->toDataObject([])->dto);
        $this->assertSame([], $mapper->toArray(new stdClass()));
    }

    public function test_simple()
    {
        $mapper = new PublicPropertyDataMapper(SimpleRequest::class);

        $this->assertSame(SimpleRequest::class, $mapper->className());
        $this->assertEquals(new SimpleRequest(), $mapper->toDataObject([])->dto);

        $dto = $mapper->toDataObject(['foo' => 'bar', 'bar' => '42']);
        $this->assertSame([], $dto->errors);
        $this->assertInstanceOf(SimpleRequest::class, $dto->dto);
        $this->assertSame('bar', $dto->dto->foo);
        $this->assertSame('42', $dto->dto->bar);

        $this->assertSame(['foo' => 'bar', 'bar' => '42'], $mapper->toArray($dto->dto));
    }

    public function test_should_ignore_non_nullable_properties()
    {
        $mapper = new PublicPropertyDataMapper(RequiredParametersRequest::class);

        $dto = $mapper->toDataObject(['foo' => null, 'bar' => null]);
        $this->assertSame([], $dto->errors);
        $this->assertInstanceOf(RequiredParametersRequest::class, $dto->dto);

        $this->assertFalse(isset($dto->dto->foo));
        $this->assertFalse(isset($dto->dto->bar));
    }
}
