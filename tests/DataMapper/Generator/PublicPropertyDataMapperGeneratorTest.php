<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\DataMapper\PublicPropertyDataMapper;

class PublicPropertyDataMapperGeneratorTest extends TestCase
{
    public function test_generate_simple()
    {
        $generator = new PublicPropertyDataMapperGenerator();
        $class = new DataMapperClass('DataMapperTestGenerateSimple');
        $generator->generate(new PublicPropertyDataMapper(SimpleRequest::class), $class);

        $this->assertEquals(<<<'PHP'
<?php

class DataMapperTestGenerateSimple implements Quatrevieux\Form\DataMapper\DataMapperInterface
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

PHP
            , $class->code()
        );

        eval(str_replace('<?php', '', $class->code()));

        $generatedDataMapper = new  \DataMapperTestGenerateSimple();

        $this->assertSame(SimpleRequest::class, $generatedDataMapper->className());

        $request = $generatedDataMapper->toDataObject([
            'foo' => 'aaa',
            'bar' => 'bbb',
        ]);

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertSame('aaa', $request->foo);
        $this->assertSame('bbb', $request->bar);
    }
}
