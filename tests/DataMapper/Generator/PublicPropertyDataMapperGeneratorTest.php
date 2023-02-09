<?php

namespace Quatrevieux\Form\DataMapper\Generator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\DataMapper\PublicPropertyDataMapper;

class PublicPropertyInstantiatorGeneratorTest extends TestCase
{
    public function test_generate_simple()
    {
        $generator = new PublicPropertyDataMapperGenerator();
        $class = new DataMapperClass('InstantiatorTestGenerateSimple');
        $generator->generate(new PublicPropertyDataMapper(SimpleRequest::class), $class);

        $this->assertEquals(<<<'PHP'
<?php

class InstantiatorTestGenerateSimple implements Quatrevieux\Form\DataMapper\DataMapperInterface
{
    /**
     * @return class-string<T>
     */
    function className(): string
    {
        return Quatrevieux\Form\Fixtures\SimpleRequest::class;
    }

    /**
     * @param array<string, mixed> $fields
     * @return T
     */
    function instantiate(array $fields): object
    {
        $object = new Quatrevieux\Form\Fixtures\SimpleRequest();
        $object->foo = $fields['foo'] ?? null;
        $object->bar = $fields['bar'] ?? null;
        return $object;
    }

    /**
     * @param T $data
     * @return array<string, mixed>
     */
    function export(object $data): array
    {
        return get_object_vars($data);
    }
}

PHP
            , $class->code()
        );

        eval(str_replace('<?php', '', $class->code()));

        $generatedInstantiator = new  \InstantiatorTestGenerateSimple();

        $this->assertSame(SimpleRequest::class, $generatedInstantiator->className());

        $request = $generatedInstantiator->instantiate([
            'foo' => 'aaa',
            'bar' => 'bbb',
        ]);

        $this->assertInstanceOf(SimpleRequest::class, $request);
        $this->assertSame('aaa', $request->foo);
        $this->assertSame('bbb', $request->bar);
    }
}
