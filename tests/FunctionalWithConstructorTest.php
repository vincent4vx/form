<?php

namespace Quatrevieux\Form;

use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\FailingTransformerRequest;
use Quatrevieux\Form\Fixtures\FooImplementation;
use Quatrevieux\Form\Fixtures\ReadonlyRequest;
use Quatrevieux\Form\Fixtures\RequestWithDefaultValue;
use Quatrevieux\Form\Fixtures\RequestWithDefaultValueConstructor;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\RequiredParametersRequestConstructor;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\SimpleRequestConstructor;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithChoiceRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyTransformerRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyTransformerRequestConstructor;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\Fixtures\WithFieldNameMappingConstructor;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\Fixtures\WithTransformerRequestConstructor;
use Quatrevieux\Form\Transformer\Field\TransformationError;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\SelectTemplate;

use function var_dump;

class FunctionalWithConstructorTest extends FormTestCase
{
    public function test_submit_simple_constructor_success_should_instantiate_dto()
    {
        $form = $this->form(SimpleRequestConstructor::class);

        $submitted = $form->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $this->assertTrue($submitted->valid());
        $this->assertInstanceOf(SimpleRequestConstructor::class, $submitted->value());
        $this->assertSame('aaa', $submitted->value()->foo);
        $this->assertSame('bbb', $submitted->value()->bar);
    }

    public function test_submit_with_required_errors()
    {
        $form = $this->form(RequiredParametersRequestConstructor::class);

        $submitted = $form->submit([]);

        $this->assertFalse($submitted->valid());
        $this->assertEquals([
            'foo' => 'This value is required',
            'bar' => 'bar must be set',
        ], $submitted->errors());

        $this->assertSame(0, $submitted->value()->foo);
        $this->assertSame('', $submitted->value()->bar);
    }

    public function test_submit_with_constraint_error()
    {
        $form = $this->form(RequiredParametersRequestConstructor::class);

        $submitted = $form->submit(['foo' => 3, 'bar' => 'a']);

        $this->assertFalse($submitted->valid());
        $this->assertSame(3, $submitted->value()->foo);
        $this->assertSame('a', $submitted->value()->bar);
        $this->assertEquals([
            'bar' => 'The value is too short. It should have 3 characters or more.',
        ], $submitted->errors());
    }

    public function test_submit_with_constraint_error_translated()
    {
        $this->configureTranslator('fr', [
            'This value is required' => 'Ce champ est requis',
            'The value is too short. It should have {{ min }} characters or more.' => 'La valeur est trop courte. Elle doit avoir au moins {{ min }} caractères.',
        ]);

        $form = $this->form(RequiredParametersRequestConstructor::class);

        $submitted = $form->submit(['bar' => 'a']);

        $this->assertFalse($submitted->valid());
        $this->assertEquals([
            'foo' => 'Ce champ est requis',
            'bar' => 'La valeur est trop courte. Elle doit avoir au moins 3 caractères.',
        ], $submitted->errors());
    }

    public function test_submit_with_constraint_success()
    {
        $form = $this->form(RequiredParametersRequestConstructor::class);

        $submitted = $form->submit(['foo' => 3, 'bar' => 'aaa']);

        $this->assertTrue($submitted->valid());
        $this->assertSame(3, $submitted->value()->foo);
        $this->assertSame('aaa', $submitted->value()->bar);
        $this->assertEmpty($submitted->errors());
    }

    public function test_submit_with_incompatible_data_type_should_be_filtered()
    {
        $form = $this->form(SimpleRequestConstructor::class);

        $submitted = $form->submit(['foo' => ['bar'], 'bar' => new \stdClass()]);

        $this->assertNull($submitted->value()->foo);
        $this->assertNull($submitted->value()->bar);
    }

    public function test_with_transformer()
    {
        $form = $this->form(WithTransformerRequestConstructor::class);

        $submitted = $form->submit(['list' => 'foo,bar,baz']);

        $this->assertTrue($submitted->valid());
        $this->assertSame(['foo', 'bar', 'baz'], $submitted->value()->list);
    }

    public function test_import_simple()
    {
        $request = new SimpleRequestConstructor('aaa', 'bbb');

        $imported = $this->form(SimpleRequestConstructor::class)->import($request);

        $this->assertSame($request, $imported->value());
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $imported->httpValue());
    }

    public function test_import_with_transformer()
    {
        $request = new WithTransformerRequestConstructor(['a"aa', 'b,bb', 'ccc']);

        $imported = $this->form(WithTransformerRequestConstructor::class)->import($request);

        $this->assertSame($request, $imported->value());
        $this->assertSame(['list' => '"a""aa","b,bb",ccc'], $imported->httpValue());
    }

    public function test_with_transformer_with_dependencies()
    {
        $this->container->set(FooImplementation::class, new FooImplementation('zsx'));

        $submitted = $this->form(WithExternalDependencyTransformerRequestConstructor::class)->submit(['foo' => 'bar']);

        $this->assertSame('zsxbaraqw', $submitted->value()->foo);
    }

    public function test_http_field_mapping()
    {
        $form = $this->form(WithFieldNameMappingConstructor::class);

        $submitted = $form->submit(['my_complex_name' => 'foo', 'other' => 123]);

        $this->assertSame('foo', $submitted->value()->myComplexName);
        $this->assertSame(123, $submitted->value()->otherField);

        $obj = new WithFieldNameMappingConstructor('bar', 456);

        $this->assertSame(['my_complex_name' => 'bar', 'other' => 456], $form->import($obj)->httpValue());
    }

    public function test_view_simple_form()
    {
        $form = $this->form(SimpleRequestConstructor::class);
        $view = $form->view();

        $this->assertSame(SimpleRequestConstructor::class, $view->class);
        $this->assertEquals([
            'foo' => new FieldView('foo', null, null, []),
            'bar' => new FieldView('bar', null, null, []),
        ], $view->fields);
        $this->assertSame([], $view->value);

        $view = $form->submit(['foo' => 'aaa', 'bar' => 'bbb'])->view();

        $this->assertSame(SimpleRequestConstructor::class, $view->class);
        $this->assertEquals([
            'foo' => new FieldView('foo', 'aaa', null, []),
            'bar' => new FieldView('bar', 'bbb', null, []),
        ], $view->fields);
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $view->value);

        $request = new SimpleRequestConstructor('ccc', 'ddd');

        $view = $form->import($request)->view();

        $this->assertSame(SimpleRequestConstructor::class, $view->class);
        $this->assertEquals([
            'foo' => new FieldView('foo', 'ccc', null, []),
            'bar' => new FieldView('bar', 'ddd', null, []),
        ], $view->fields);
        $this->assertSame(['foo' => 'ccc', 'bar' => 'ddd'], $view->value);
    }

    public function test_view_with_constraint_error()
    {
        $form = $this->form(RequiredParametersRequestConstructor::class);

        $view = $form->submit(['bar' => 'a'])->view();

        $this->assertSame(RequiredParametersRequestConstructor::class, $view->class);
        $this->assertEquals([
            'foo' => new FieldView('foo', null, new FieldError('This value is required', [], Required::CODE, DummyTranslator::instance()), ['required' => true]),
            'bar' => new FieldView('bar', 'a', new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE, DummyTranslator::instance()), ['required' => true, 'minlength' => 3]),
        ], $view->fields);
        $this->assertSame(['bar' => 'a'], $view->value);

        $this->assertEquals('<input name="foo" value="" required />', (string) $view->fields['foo']);
        $this->assertEquals('<input name="bar" value="a" required minlength="3" />', (string) $view->fields['bar']);
    }

    public function test_import_then_submit_should_perform_patch()
    {
        $form = $this->form(RequiredParametersRequestConstructor::class);

        $request = new RequiredParametersRequestConstructor(456, 'azerty');

        $submitted = $form->import($request)->submit([]);

        $this->assertTrue($submitted->valid());
        $this->assertEquals($request, $submitted->value());

        $submitted = $form->import($request)->submit(['foo' => 123]);

        $this->assertTrue($submitted->valid());
        $this->assertNotEquals($request, $submitted->value());
        $this->assertSame(123, $submitted->value()->foo);
        $this->assertSame('azerty', $submitted->value()->bar);
    }

    public function test_submit_twice_will_perform_patch()
    {
        $form = $this->form(RequiredParametersRequestConstructor::class);

        $submitted = $form->submit(['foo' => '123'])->submit(['bar' => 'azerty']);

        $this->assertTrue($submitted->valid());
        $this->assertSame(123, $submitted->value()->foo);
        $this->assertSame('azerty', $submitted->value()->bar);

        $submitted = $submitted->submit(['foo' => '456']);

        $this->assertTrue($submitted->valid());
        $this->assertSame(456, $submitted->value()->foo);
        $this->assertSame('azerty', $submitted->value()->bar);

        $submitted = $submitted->submit(['foo' => null]);

        $this->assertFalse($submitted->valid());
        $this->assertErrors(['foo' => 'This value is required'], $submitted->errors());
    }

    public function test_default_value()
    {
        $form = $this->form(RequestWithDefaultValueConstructor::class);

        $submitted = $form->submit([]);

        $this->assertTrue($submitted->valid());
        $this->assertSame(42, $submitted->value()->foo);
        $this->assertSame('???', $submitted->value()->bar);

        $submitted = $form->submit([
            'foo' => 123,
            'bar' => 'abc',
        ]);

        $this->assertTrue($submitted->valid());
        $this->assertSame(123, $submitted->value()->foo);
        $this->assertSame('abc', $submitted->value()->bar);
    }

    public function form(string $dataClass): FormInterface
    {
        return $this->runtimeForm($dataClass);
    }
}
