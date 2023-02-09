<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DataMapper\DataMapperInterface;
use Quatrevieux\Form\DataMapper\PublicPropertyDataMapper;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;

class DataMapperGeneratorTest extends TestCase
{
    private DataMapperGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new DataMapperGenerator();
    }

    public function test_generate_simple()
    {
        $code = $this->generator->generate(
            'DataMapperWithSimpleProperties',
            new PublicPropertyDataMapper(SimpleRequest::class)
        );

        $this->assertEquals(<<<'PHP'
<?php

class DataMapperWithSimpleProperties implements Quatrevieux\Form\DataMapper\DataMapperInterface
{
    function className(): string
    {
        return Quatrevieux\Form\Fixtures\SimpleRequest::class;
    }

    function toDataObject(array $fields): object
    {
        $object = new Quatrevieux\Form\Fixtures\SimpleRequest();
        $object->foo = $fields['foo'] ?? null;
        $object->bar = $fields['bar'] ?? null;
        return $object;
    }

    function toArray(object $data): array
    {
        return get_object_vars($data);
    }
}

PHP, $code);
    }

    public function test_generate_with_non_nullable_properties()
    {
        $code = $this->generator->generate(
            'DataMapperWithNonNullableProperties',
            new PublicPropertyDataMapper(RequiredParametersRequest::class)
        );

        $this->assertEquals(<<<'PHP'
<?php

class DataMapperWithNonNullableProperties implements Quatrevieux\Form\DataMapper\DataMapperInterface
{
    function className(): string
    {
        return Quatrevieux\Form\Fixtures\RequiredParametersRequest::class;
    }

    function toDataObject(array $fields): object
    {
        $object = new Quatrevieux\Form\Fixtures\RequiredParametersRequest();
        if (($__tmp_acbd18db4cc2f85cedef654fccc4a4d8 = $fields['foo'] ?? null) !== null) {
            $object->foo = $__tmp_acbd18db4cc2f85cedef654fccc4a4d8;
        }
        if (($__tmp_37b51d194a7513e45b56f6524f2d51f2 = $fields['bar'] ?? null) !== null) {
            $object->bar = $__tmp_37b51d194a7513e45b56f6524f2d51f2;
        }
        return $object;
    }

    function toArray(object $data): array
    {
        return get_object_vars($data);
    }
}

PHP, $code);
    }

    public function test_unsupported_data_mapper()
    {
        $this->assertNull($this->generator->generate('DataMapperWithUnsupportedDataMapper', $this->createMock(DataMapperInterface::class)));
    }
}
