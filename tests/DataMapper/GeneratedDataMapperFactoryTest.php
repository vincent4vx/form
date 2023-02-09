<?php

namespace Quatrevieux\Form\DataMapper;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;

class GeneratedDataMapperFactoryTest extends TestCase
{
    private GeneratedDataMapperFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new GeneratedDataMapperFactory(
            savePathResolver: fn(string $className) => __DIR__.'/_tmp/'.$className.'.php',
            classNameResolver: fn(string $dataClass) => 'Test'.(new \ReflectionClass($dataClass))->getShortName().'DataMapper'
        );
    }

    protected function tearDown(): void
    {
        if (!is_dir(__DIR__.'/_tmp/')) {
            return;
        }

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

        $this->assertInstanceOf(DataMapperInterface::class, $instantiator);
        $this->assertInstanceOf('TestSimpleRequestDataMapper', $instantiator);
        $this->assertFileExists(__DIR__.'/_tmp/TestSimpleRequestDataMapper.php');
        $this->assertEquals(<<<'PHP'
<?php

class TestSimpleRequestDataMapper implements Quatrevieux\Form\DataMapper\DataMapperInterface
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
        , file_get_contents(__DIR__.'/_tmp/TestSimpleRequestDataMapper.php')
);
    }

    public function test_create_should_load_and_instantiate_generated_instantiator()
    {
        $this->factory->create(SimpleRequest::class);
        $this->assertInstanceOf(DataMapperInterface::class, $this->factory->create(SimpleRequest::class));
        $this->assertSame(SimpleRequest::class, $this->factory->create(SimpleRequest::class)->className());
        $this->assertInstanceOf('TestSimpleRequestDataMapper', $this->factory->create(SimpleRequest::class));
    }
}
