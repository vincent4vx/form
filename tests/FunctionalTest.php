<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\FooImplementation;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyTransformerRequest;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;

class FunctionalTest extends FormTestCase
{
    public function test_submit_simple_success_should_instantiate_dto()
    {
        $form = $this->form(SimpleRequest::class);

        $submitted = $form->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $this->assertTrue($submitted->valid());
        $this->assertSame('aaa', $submitted->value()->foo);
        $this->assertSame('bbb', $submitted->value()->bar);
    }

    public function test_submit_with_required_errors()
    {
        $form = $this->form(RequiredParametersRequest::class);

        $submitted = $form->submit([]);

        $this->assertFalse($submitted->valid());
        $this->assertEquals([
            'foo' => 'This value is required',
            'bar' => 'bar must be set',
        ], $submitted->errors());
    }

    public function test_submit_with_constraint_error()
    {
        $form = $this->form(RequiredParametersRequest::class);

        $submitted = $form->submit(['foo' => 3, 'bar' => 'a']);

        $this->assertFalse($submitted->valid());
        $this->assertSame(3, $submitted->value()->foo);
        $this->assertSame('a', $submitted->value()->bar);
        $this->assertEquals([
            'bar' => 'Invalid length',
        ], $submitted->errors());
    }

    public function test_submit_with_constraint_success()
    {
        $form = $this->form(RequiredParametersRequest::class);

        $submitted = $form->submit(['foo' => 3, 'bar' => 'aaa']);

        $this->assertTrue($submitted->valid());
        $this->assertSame(3, $submitted->value()->foo);
        $this->assertSame('aaa', $submitted->value()->bar);
        $this->assertEmpty($submitted->errors());
    }

    public function test_submit_with_incompatible_data_type_should_be_filtered()
    {
        $form = $this->form(SimpleRequest::class);

        $submitted = $form->submit(['foo' => ['bar'], 'bar' => new \stdClass()]);

        $this->assertNull($submitted->value()->foo);
        $this->assertNull($submitted->value()->bar);
    }

    public function test_with_transformer()
    {
        $form = $this->form(WithTransformerRequest::class);

        $submitted = $form->submit(['list' => 'foo,bar,baz']);

        $this->assertTrue($submitted->valid());
        $this->assertSame(['foo', 'bar', 'baz'], $submitted->value()->list);
    }

    public function test_import_simple()
    {
        $request = new SimpleRequest();
        $request->foo = 'aaa';
        $request->bar = 'bbb';

        $imported = $this->form(SimpleRequest::class)->import($request);

        $this->assertSame($request, $imported->value());
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $imported->httpValue());
    }

    public function test_import_with_transformer()
    {
        $request = new WithTransformerRequest();
        $request->list = ['a"aa', 'b,bb', 'ccc'];

        $imported = $this->form(WithTransformerRequest::class)->import($request);

        $this->assertSame($request, $imported->value());
        $this->assertSame(['list' => '"a""aa","b,bb",ccc'], $imported->httpValue());
    }

    public function test_with_transformer_with_dependencies()
    {
        $this->container->set(FooImplementation::class, new FooImplementation('zsx'));

        $submitted = $this->form(WithExternalDependencyTransformerRequest::class)->submit(['foo' => 'bar']);

        $this->assertSame('zsxbaraqw', $submitted->value()->foo);
    }

    public function test_with_constraint_with_dependencies()
    {
        $this->container->set(TestConfig::class, new TestConfig(['foo.length' => 5]));
        $this->container->set(ConfiguredLengthValidator::class, new ConfiguredLengthValidator($this->container->get(TestConfig::class)));

        $submitted = $this->form(WithExternalDependencyConstraintRequest::class)->submit(['foo' => 'bar']);

        $this->assertTrue($submitted->valid());
        $this->assertSame('bar', $submitted->value()->foo);

        $submitted = $this->form(WithExternalDependencyConstraintRequest::class)->submit(['foo' => 'barbaz']);

        $this->assertFalse($submitted->valid());
        $this->assertSame('barbaz', $submitted->value()->foo);
        $this->assertEquals('Invalid length', $submitted->errors()['foo']);
    }

    public function form(string $dataClass): FormInterface
    {
        return $this->runtimeForm($dataClass);
    }
}
