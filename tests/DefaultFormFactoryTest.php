<?php

namespace Quatrevieux\Form;

use BadMethodCallException;
use PhpBench\Reflection\ReflectionClass;
use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\View\FormView;
use ReflectionProperty;

class DefaultFormFactoryTest extends TestCase
{
    public function test_runtime()
    {
        $factory = DefaultFormFactory::runtime();

        $this->assertInstanceOf(DefaultFormFactory::class, $factory);
    }

    public function test_runtime_with_registry()
    {
        $registry = new DefaultRegistry();
        $factory = DefaultFormFactory::runtime($registry);

        $registry->registerValidator(new ConfiguredLengthValidator(new TestConfig(['foo.length' => 3])));

        $form = $factory->create(WithExternalDependencyConstraintRequest::class);

        $this->assertTrue($form->submit(['foo' => '12'])->valid());
        $this->assertFalse($form->submit(['foo' => '1234'])->valid());
    }

    public function test_create()
    {
        $factory = DefaultFormFactory::runtime();
        $form = $factory->create(SimpleRequest::class);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(SimpleRequest::class, $form->submit([])->value());

        $this->assertSame($form, $factory->create(SimpleRequest::class));
    }

    public function test_import()
    {
        $factory = DefaultFormFactory::runtime();

        $data = new SimpleRequest();
        $data->foo = 'aaa';
        $data->bar = 'bbb';

        $form = $factory->import($data);

        $this->assertInstanceOf(ImportedForm::class, $form);
        $this->assertSame($data, $form->value());
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $form->httpValue());
    }

    public function test_create_without_view()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('View system disabled for the form');

        $factory = DefaultFormFactory::runtime(enabledView: false);
        $factory->create(SimpleRequest::class)->view();
    }

    public function test_generated()
    {
        $factory = DefaultFormFactory::generated(
            savePathResolver: Functions::savePathResolver(__DIR__ . '/_tmp')
        );
        $form = $factory->create(SimpleRequest::class);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(SimpleRequest::class, $form->submit([])->value());
        $this->assertInstanceOf(FormView::class, $form->view());

        $transformer = (new ReflectionProperty($form, 'transformer'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestTransformer', $transformer);

        $validator = (new ReflectionProperty($form, 'validator'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestValidator', $validator);

        $dataMapper = (new ReflectionProperty($form, 'dataMapper'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestDataMapper', $dataMapper);

        $viewInstantiator = (new ReflectionProperty($form, 'viewInstantiator'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestViewInstantiator', $viewInstantiator);
    }

    public function test_generated_without_view()
    {
        $factory = DefaultFormFactory::generated(
            savePathResolver: Functions::savePathResolver(__DIR__ . '/_tmp'),
            enabledView: false,
        );
        $form = $factory->create(SimpleRequest::class);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(SimpleRequest::class, $form->submit([])->value());

        $transformer = (new ReflectionProperty($form, 'transformer'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestTransformer', $transformer);

        $validator = (new ReflectionProperty($form, 'validator'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestValidator', $validator);

        $dataMapper = (new ReflectionProperty($form, 'dataMapper'))->getValue($form);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestDataMapper', $dataMapper);

        $viewInstantiator = (new ReflectionProperty($form, 'viewInstantiator'))->getValue($form);
        $this->assertNull($viewInstantiator);
    }
}
