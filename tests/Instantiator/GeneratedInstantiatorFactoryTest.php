<?php

namespace Quatrevieux\Form\Instantiator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;

class GeneratedInstantiatorFactoryTest extends TestCase
{
    private GeneratedInstantiatorFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new GeneratedInstantiatorFactory(
            savePathResolver: fn(string $className) => __DIR__.'/_tmp/'.$className.'.php',
            classNameResolver: fn(string $dataClass) => 'Test'.(new \ReflectionClass($dataClass))->getShortName().'Instantiator'
        );
    }

    protected function tearDown(): void
    {
        foreach (scandir(__DIR__.'/_tmp/') as $file) {
            $file = __DIR__.'/_tmp/'.$file;

            if (is_file($file)) {
                unlink($file);
            }
        }

        @rmdir(__DIR__.'/_tmp/');
    }

    public function test_create_should_generate_instantiator_class()
    {
        $instantiator = $this->factory->create(SimpleRequest::class);

        $this->assertInstanceOf(PublicPropertyInstantiator::class, $instantiator);
        $this->assertFileExists(__DIR__.'/_tmp/TestSimpleRequestInstantiator.php');
        $this->assertEquals(<<<'PHP'
<?php

class TestSimpleRequestInstantiator implements Quatrevieux\Form\Instantiator\InstantiatorInterface
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

    /**
     * @param T $data
     * @return array
     */
    function export(object $data): array
    {
        return get_object_vars($data);
    }
}

PHP
        , file_get_contents(__DIR__.'/_tmp/TestSimpleRequestInstantiator.php')
);
    }

    public function test_create_should_load_and_instantiate_generated_instantiator()
    {
        $this->factory->create(SimpleRequest::class);
        $this->assertInstanceOf(InstantiatorInterface::class, $this->factory->create(SimpleRequest::class));
        $this->assertSame(SimpleRequest::class, $this->factory->create(SimpleRequest::class)->className());
        $this->assertInstanceOf('TestSimpleRequestInstantiator', $this->factory->create(SimpleRequest::class));
    }
}
