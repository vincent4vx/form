<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DataMapper\DataMapperFactoryInterface;
use Quatrevieux\Form\DataMapper\DataMapperInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\FormTransformerFactoryInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\ValidatorFactoryInterface;
use Quatrevieux\Form\View\FormViewInstantiatorFactoryInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DefaultRegistryTest extends TestCase
{
    public function test_getTransformer_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transformer "foo" is not registered');

        $registry = new DefaultRegistry();
        $registry->getFieldTransformer('foo');
    }

    public function test_getValidator_not_found()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Validator "foo" is not registered');

        $registry = new DefaultRegistry();
        $registry->getConstraintValidator('foo');
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
        $this->assertSame($transformer, $registry->getFieldTransformer(get_class($transformer)));

        $registry->registerTransformer($transformer = $this->createMock(ConfigurableFieldTransformerInterface::class), 'foo');
        $this->assertSame($transformer, $registry->getFieldTransformer('foo'));
    }

    public function test_registerValidator()
    {
        $registry = new DefaultRegistry();

        $registry->registerValidator($validator = $this->createMock(ConstraintValidatorInterface::class));
        $this->assertSame($validator, $registry->getConstraintValidator(get_class($validator)));

        $registry->registerValidator($validator = $this->createMock(ConstraintValidatorInterface::class), 'foo');
        $this->assertSame($validator, $registry->getConstraintValidator('foo'));
    }

    public function test_get_set_modules()
    {
        $registry = new DefaultRegistry();

        $instantiatorFactory = $this->createMock(DataMapperFactoryInterface::class);
        $transformerFactory = $this->createMock(FormTransformerFactoryInterface::class);
        $validatorFactory = $this->createMock(ValidatorFactoryInterface::class);
        $viewInstantiatorFactory = $this->createMock(FormViewInstantiatorFactoryInterface::class);

        $registry->setDataMapperFactory($instantiatorFactory);
        $registry->setTransformerFactory($transformerFactory);
        $registry->setValidatorFactory($validatorFactory);
        $registry->setFormViewInstantiatorFactory($viewInstantiatorFactory);

        $this->assertSame($instantiatorFactory, $registry->getDataMapperFactory());
        $this->assertSame($transformerFactory, $registry->getTransformerFactory());
        $this->assertSame($validatorFactory, $registry->getValidatorFactory());
        $this->assertSame($viewInstantiatorFactory, $registry->getFormViewInstantiatorFactory());
    }
}
