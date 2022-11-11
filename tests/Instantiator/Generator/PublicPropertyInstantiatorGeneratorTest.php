<?php

namespace Quatrevieux\Form\Instantiator\Generator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Instantiator\PublicPropertyInstantiator;

class PublicPropertyInstantiatorGeneratorTest extends TestCase
{
    public function test_generate_simple()
    {
        $generator = new PublicPropertyInstantiatorGenerator();
        $class = new InstantiatorClass('InstantiatorTestGenerateSimple');
        $generator->generate(new PublicPropertyInstantiator(SimpleRequest::class), $class);

        $this->assertEquals(<<<'PHP'
<?php

class InstantiatorTestGenerateSimple implements Quatrevieux\Form\Instantiator\InstantiatorInterface
{
    /**
     * @return class-string<T>
     */
    function className(): string
    {
        return Quatrevieux\Form\Fixtures\SimpleRequest::class;
    }

    /**
     * @param array $fields
     * @return T
     */
    function instantiate(array $fields): object
    {
        $object = new Quatrevieux\Form\Fixtures\SimpleRequest();
        $object->foo = $fields['foo'] ?? null;
        $object->bar = $fields['bar'] ?? null;
        return $object;
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
