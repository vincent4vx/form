<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Quatrevieux\Form\Fixtures\SimpleRequest;

class DefaultFormFactoryTest extends TestCase
{
    public function test_runtime()
    {
        $container = $this->createMock(ContainerInterface::class);
        $factory = DefaultFormFactory::runtime($container);

        $this->assertInstanceOf(DefaultFormFactory::class, $factory);
    }

    public function test_create()
    {
        $factory = DefaultFormFactory::runtime($this->createMock(ContainerInterface::class));
        $form = $factory->create(SimpleRequest::class);

        $this->assertInstanceOf(Form::class, $form);
        $this->assertInstanceOf(SimpleRequest::class, $form->submit([])->value());
    }
}
