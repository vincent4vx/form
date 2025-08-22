<?php

namespace Quatrevieux\Form;

use http\Exception\InvalidArgumentException;
use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\FailingTransformerRequest;
use Quatrevieux\Form\Fixtures\FooImplementation;
use Quatrevieux\Form\Fixtures\ReadonlyRequest;
use Quatrevieux\Form\Fixtures\RequestWithDefaultValue;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithChoiceRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\Fixtures\WithExternalDependencyTransformerRequest;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\Transformer\Field\TransformationError;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\Required;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\SelectTemplate;

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

    public function test_submit_with_constraint_error_translated()
    {
        $this->configureTranslator('fr', [
            'This value is required' => 'Ce champ est requis',
            'The value is too short. It should have {{ min }} characters or more.' => 'La valeur est trop courte. Elle doit avoir au moins {{ min }} caractères.',
        ]);

        $form = $this->form(RequiredParametersRequest::class);

        $submitted = $form->submit(['bar' => 'a']);

        $this->assertFalse($submitted->valid());
        $this->assertEquals([
            'foo' => 'Ce champ est requis',
            'bar' => 'La valeur est trop courte. Elle doit avoir au moins 3 caractères.',
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
        $this->assertErrors(['foo' => new FieldError('Syntax error', code: TransformationError::CODE)], $submitted->errors());

        $submitted = $form->submit(['foo' => '123']);
        $this->assertFalse($submitted->valid());
        $this->assertFalse(isset($submitted->value()->foo));
        $this->assertErrors(['foo' => new FieldError('Invalid JSON object', code: TransformationError::CODE)], $submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}']);
        $this->assertTrue($submitted->valid());
        $this->assertEquals((object) ['foo' => 'bar'], $submitted->value()->foo);
        $this->assertEmpty($submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}', 'customTransformerErrorHandling' => '____']);
        $this->assertFalse($submitted->valid());
        $this->assertSame('____', $submitted->value()->customTransformerErrorHandling);
        $this->assertErrors(['customTransformerErrorHandling' => new FieldError('invalid data', code: 'd2e95635-fdb6-4752-acb4-aa8f76f64de6')], $submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}', 'customTransformerErrorHandling' => base64_encode('foo')]);
        $this->assertTrue($submitted->valid());
        $this->assertSame('foo', $submitted->value()->customTransformerErrorHandling);
        $this->assertEmpty($submitted->errors());

        $submitted = $form->submit(['foo' => '{"foo":"bar"}', 'ignoreError' => '____']);
        $this->assertTrue($submitted->valid());
        $this->assertNull($submitted->value()->ignoreError);
        $this->assertEmpty($submitted->errors());

        $this->configureTranslator('fr', [
            'Syntax error' => 'Erreur de syntaxe',
            'Invalid JSON object' => 'Objet JSON invalide',
            'invalid data' => 'données invalides',
        ]);

        $this->assertErrors(['foo' => 'Erreur de syntaxe'], $form->submit(['foo' => 'foo'])->errors());
        $this->assertErrors(['foo' => 'Objet JSON invalide'], $form->submit(['foo' => '123'])->errors());
        $this->assertErrors(['customTransformerErrorHandling' => 'données invalides'], $form->submit(['foo' => '{"foo":"bar"}', 'customTransformerErrorHandling' => '____'])->errors());
    }

    public function test_view_simple_form()
    {
        $form = $this->form(SimpleRequest::class);
        $view = $form->view();

        $this->assertEquals([
            'foo' => new FieldView('foo', null, null, []),
            'bar' => new FieldView('bar', null, null, []),
        ], $view->fields);
        $this->assertSame([], $view->value);

        $view = $form->submit(['foo' => 'aaa', 'bar' => 'bbb'])->view();

        $this->assertEquals([
            'foo' => new FieldView('foo', 'aaa', null, []),
            'bar' => new FieldView('bar', 'bbb', null, []),
        ], $view->fields);
        $this->assertSame(['foo' => 'aaa', 'bar' => 'bbb'], $view->value);

        $request = new SimpleRequest();
        $request->foo = 'ccc';
        $request->bar = 'ddd';

        $view = $form->import($request)->view();

        $this->assertEquals([
            'foo' => new FieldView('foo', 'ccc', null, []),
            'bar' => new FieldView('bar', 'ddd', null, []),
        ], $view->fields);
        $this->assertSame(['foo' => 'ccc', 'bar' => 'ddd'], $view->value);
    }

    public function test_http_field_mapping_view()
    {
        $form = $this->form(WithFieldNameMapping::class);

        $view = $form->view();
        $this->assertEquals([
            'myComplexName' => new FieldView('my_complex_name', null, null, []),
            'otherField' => new FieldView('other', null, null, []),
        ], $view->fields);
        $this->assertSame([], $view->value);

        $view = $form->submit(['my_complex_name' => 'foo', 'other' => 123])->view();

        $this->assertEquals([
            'myComplexName' => new FieldView('my_complex_name', 'foo', null, []),
            'otherField' => new FieldView('other', 123, null, []),
        ], $view->fields);
        $this->assertSame(['my_complex_name' => 'foo', 'other' => 123], $view->value);

        $obj = new WithFieldNameMapping();
        $obj->myComplexName = 'bar';
        $obj->otherField = 456;

        $view = $form->import($obj)->view();
        $this->assertEquals([
            'myComplexName' => new FieldView('my_complex_name', 'bar', null, []),
            'otherField' => new FieldView('other', 456, null, []),
        ], $view->fields);
        $this->assertSame(['my_complex_name' => 'bar', 'other' => 456], $view->value);

        $this->assertEquals('<input name="my_complex_name" value="bar" />', (string) $view->fields['myComplexName']);
        $this->assertEquals('<input name="other" value="456" />', (string) $view->fields['otherField']);
    }

    public function test_view_with_constraint_error()
    {
        $form = $this->form(RequiredParametersRequest::class);

        $view = $form->submit(['bar' => 'a'])->view();

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
        $form = $this->form(RequiredParametersRequest::class);

        $request = new RequiredParametersRequest();
        $request->foo = 456;
        $request->bar = 'azerty';

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
        $form = $this->form(RequiredParametersRequest::class);

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
        $form = $this->form(RequestWithDefaultValue::class);

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

    public function test_readonly_request()
    {
        if (PHP_VERSION_ID < 80400) {
            $this->markTestSkipped('Readonly properties are supported since PHP 8.4');
        }

        $form = $this->form(ReadonlyRequest::class);

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

    public function test_with_choice()
    {
        $form = $this->form(WithChoiceRequest::class);
        $view = $form->view();

        $this->assertEquals('<select name="value" ><option value="42" >The answer</option><option value="666" >The beast</option><option value="404" >Lost</option></select>', $view['value']->render(SelectTemplate::Select));

        $submitted = $form->submit(['value' => '666']);
        $this->assertTrue($submitted->valid());
        $this->assertSame(666, $submitted->value()->value);
        $this->assertEquals('<select name="value" ><option value="42" >The answer</option><option value="666" selected>The beast</option><option value="404" >Lost</option></select>', $submitted->view()['value']->render(SelectTemplate::Select));

        $submitted = $form->submit(['value' => '-1']);
        $this->assertFalse($submitted->valid());
        $this->assertErrors(['value' => 'The value is not a valid choice.'], $submitted->errors());
    }

    public function form(string $dataClass): FormInterface
    {
        return $this->runtimeForm($dataClass);
    }
}
