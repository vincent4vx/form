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
        $this->assertEquals(new stdClass(), $mapper->toDataObject([]));
        $this->assertSame([], $mapper->toArray(new stdClass()));
    }

    public function test_simple()
    {
        $mapper = new PublicPropertyDataMapper(SimpleRequest::class);

        $this->assertSame(SimpleRequest::class, $mapper->className());
        $this->assertEquals(new SimpleRequest(), $mapper->toDataObject([]));

        $dto = $mapper->toDataObject(['foo' => 'bar', 'bar' => '42']);
        $this->assertInstanceOf(SimpleRequest::class, $dto);
        $this->assertSame('bar', $dto->foo);
        $this->assertSame('42', $dto->bar);

        $this->assertSame(['foo' => 'bar', 'bar' => '42'], $mapper->toArray($dto));
    }

    public function test_should_ignore_non_nullable_properties()
    {
        $mapper = new PublicPropertyDataMapper(RequiredParametersRequest::class);

        $dto = $mapper->toDataObject(['foo' => null, 'bar' => null]);
        $this->assertInstanceOf(RequiredParametersRequest::class, $dto);

        $this->assertFalse(isset($dto->foo));
        $this->assertFalse(isset($dto->bar));
    }
}
