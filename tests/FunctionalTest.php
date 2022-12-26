<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\FailingTransformerRequest;
use Quatrevieux\Form\Fixtures\FooImplementation;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyTransformerRequest;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\Validator\FieldError;

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
            'bar' => 'The value is too short. It should have 3 characters or more.',
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

    public function test_http_field_mapping()
    {
        $form = $this->form(WithFieldNameMapping::class);

        $submitted = $form->submit(['my_complex_name' => 'foo', 'other' => 123]);

        $this->assertSame('foo', $submitted->value()->myComplexName);
        $this->assertSame(123, $submitted->value()->otherField);

        $obj = new WithFieldNameMapping();
        $obj->myComplexName = 'bar';
        $obj->otherField = 456;

        $this->assertSame(['my_complex_name' => 'bar', 'other' => 456], $form->import($obj)->httpValue());
    }

    public function test_with_transformation_error()
    {
        $form = $this->form(FailingTransformerRequest::class);

        $submitted = $form->submit(['foo' => 'foo']);
        $this->assertFalse($submitted->valid());
        $this->assertFalse(isset($submitted->value()->foo));
        $this->assertEquals(['foo' => new FieldError('Syntax error')], $submitted->errors());

        $submitted = $form->submit(['foo' => '123']);
        $this->assertFalse($submitted->valid());
        $this->assertFalse(isset($submitted->value()->foo));
        $this->assertEquals(['foo' => new FieldError('Invalid JSON object')], $submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}']);
        $this->assertTrue($submitted->valid());
        $this->assertEquals((object) ['foo' => 'bar'], $submitted->value()->foo);
        $this->assertEmpty($submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}', 'customTransformerErrorHandling' => '____']);
        $this->assertFalse($submitted->valid());
        $this->assertSame('____', $submitted->value()->customTransformerErrorHandling);
        $this->assertEquals(['customTransformerErrorHandling' => new FieldError('invalid data')], $submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}', 'customTransformerErrorHandling' => base64_encode('foo')]);
        $this->assertTrue($submitted->valid());
        $this->assertSame('foo', $submitted->value()->customTransformerErrorHandling);
        $this->assertEmpty($submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}', 'ignoreError' => '____']);
        $this->assertTrue($submitted->valid());
        $this->assertNull($submitted->value()->ignoreError);
        $this->assertEmpty($submitted->errors());

    }

    public function form(string $dataClass): FormInterface
    {
        return $this->runtimeForm($dataClass);
    }
}
