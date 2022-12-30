<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;

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
    }
}
