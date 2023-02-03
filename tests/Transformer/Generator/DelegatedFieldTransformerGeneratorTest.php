<?php

namespace Quatrevieux\Form\Transformer\Generator;

use Attribute;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Transformer\Field\DelegatedFieldTransformerInterface;

class DelegatedFieldTransformerGeneratorTest extends FormTestCase
{
    public function test_generate()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('($__transformer_ff4a55a184baa579d830825fffb6cff2 = new \Quatrevieux\Form\Transformer\Generator\MyCustomDelegatedTransformer(foo: 5))->getTransformer($this->registry)->transformToHttp($__transformer_ff4a55a184baa579d830825fffb6cff2, $data["foo"] ?? null)', (new DelegatedFieldTransformerGenerator())->generateTransformToHttp(new MyCustomDelegatedTransformer(5), '$data["foo"] ?? null', $generator));
        $this->assertSame('($__transformer_ff4a55a184baa579d830825fffb6cff2 = new \Quatrevieux\Form\Transformer\Generator\MyCustomDelegatedTransformer(foo: 5))->getTransformer($this->registry)->transformFromHttp($__transformer_ff4a55a184baa579d830825fffb6cff2, $data["foo"] ?? null)', (new DelegatedFieldTransformerGenerator())->generateTransformFromHttp(new MyCustomDelegatedTransformer(5), '$data["foo"] ?? null', $generator));
    }

    public function test_functional()
    {
        $this->container->set(MyCustomDelegatedTransformerImpl::class, new MyCustomDelegatedTransformerImpl());
        $form = $this->generatedForm(TestRequestWithDelegatedTransformer::class);

        $this->assertSame(8, $form->submit(['foo' => '5'])->value()->foo);

        $o = new TestRequestWithDelegatedTransformer;
        $o->foo = 42;

        $this->assertSame(['foo' => 39], $form->import($o)->httpValue());
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyCustomDelegatedTransformer implements DelegatedFieldTransformerInterface
{
    public function __construct(
        public int $foo,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return $registry->getFieldTransformer(MyCustomDelegatedTransformerImpl::class);
    }
}

class MyCustomDelegatedTransformerImpl implements ConfigurableFieldTransformerInterface
{
    public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return $value + $configuration->foo;
    }

    public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
    {
        return $value - $configuration->foo;
    }
}

class TestRequestWithDelegatedTransformer
{
    #[MyCustomDelegatedTransformer(3)]
    public ?int $foo;
}
