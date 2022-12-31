<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Instantiator\InstantiatorFactoryInterface;
use Quatrevieux\Form\Instantiator\InstantiatorInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultRegistryTest extends TestCase
{
    public function test_getTransformer_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transformer "foo" is not registered');

        $registry = new DefaultRegistry();
        $registry->getTransformer('foo');
    }

    public function test_getValidator_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validator "foo" is not registered');

        $registry = new DefaultRegistry();
        $registry->getValidator('foo');
    }

    public function test_get_set_translator()
    {
        $registry = new DefaultRegistry();
        $this->assertSame(DummyTranslator::instance(), $registry->getTranslator());

        $registry->setTranslator($translator = $this->createMock(TranslatorInterface::class));
        $this->assertSame($translator, $registry->getTranslator());
    }

    public function test_registerTransformer()
    {
        $registry = new DefaultRegistry();

        $registry->registerTransformer($transformer = $this->createMock(ConfigurableFieldTransformerInterface::class));
        $this->assertSame($transformer, $registry->getTransformer(get_class($transformer)));

        $registry->registerTransformer($transformer = $this->createMock(ConfigurableFieldTransformerInterface::class), 'foo');
        $this->assertSame($transformer, $registry->getTransformer('foo'));
    }

    public function test_registerValidator()
    {
        $registry = new DefaultRegistry();

        $registry->registerValidator($validator = $this->createMock(ConstraintValidatorInterface::class));
        $this->assertSame($validator, $registry->getValidator(get_class($validator)));

        $registry->registerValidator($validator = $this->createMock(ConstraintValidatorInterface::class), 'foo');
        $this->assertSame($validator, $registry->getValidator('foo'));
    }

    public function test_get_set_modules()
    {
        $registry = new DefaultRegistry();

        $instantiatorFactory = $this->createMock(InstantiatorFactoryInterface::class);
        $transformerFactory = $this->createMock(FormTransformerFactoryInterface::class);
        $validatorFactory = $this->createMock(ValidatorFactoryInterface::class);

        $registry->setInstantiatorFactory($instantiatorFactory);
        $registry->setTransformerFactory($transformerFactory);
        $registry->setValidatorFactory($validatorFactory);

        $this->assertSame($instantiatorFactory, $registry->getInstantiatorFactory());
        $this->assertSame($transformerFactory, $registry->getTransformerFactory());
        $this->assertSame($validatorFactory, $registry->getValidatorFactory());
    }
}
