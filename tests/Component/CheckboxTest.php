<?php

namespace Quatrevieux\Form\Component;

use Attribute;
use Quatrevieux\Form\Embedded\Embedded;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Validator\Constraint\IdenticalTo;
use Quatrevieux\Form\View\Provider\FieldViewAttributesProviderInterface;
use Quatrevieux\Form\View\Provider\FieldViewConfiguration;

class CheckboxTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CheckboxTestingRequest::class) : $this->runtimeForm(CheckboxTestingRequest::class);

        $this->assertFalse($form->submit([])->value()->field);
        $this->assertFalse($form->submit([])->value()->customValue);
        $this->assertFalse($form->submit(['field' => '0'])->value()->field);
        $this->assertFalse($form->submit(['customValue' => 'off'])->value()->customValue);
        $this->assertFalse($form->submit(['field' => 'other'])->value()->field);

        $this->assertTrue($form->submit(['field' => '1'])->value()->field);
        $this->assertTrue($form->submit(['customValue' => 'on'])->value()->customValue);
        $this->assertTrue($form->submit(['field' => 1])->value()->field);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CheckboxTestingRequest::class) : $this->runtimeForm(CheckboxTestingRequest::class);

        $this->assertNull($form->import(new CheckboxTestingRequest())->httpValue()['field']);
        $this->assertNull($form->import(new CheckboxTestingRequest())->httpValue()['customValue']);

        $req = new CheckboxTestingRequest();
        $req->field = true;
        $req->customValue = true;

        $this->assertSame('1', $form->import($req)->httpValue()['field']);
        $this->assertSame('on', $form->import($req)->httpValue()['customValue']);

        $req->field = false;
        $req->customValue = false;

        $this->assertNull($form->import($req)->httpValue()['field']);
        $this->assertNull($form->import($req)->httpValue()['customValue']);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_view(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CheckboxTestingRequest::class) : $this->runtimeForm(CheckboxTestingRequest::class);

        $this->assertEquals('<input name="field" value="1" type="checkbox" />', (string) $form->view()['field']);
        $this->assertEquals('<input name="customValue" value="on" type="checkbox" />', (string) $form->view()['customValue']);
        $this->assertEquals('<input name="customView" value="1" type="submit" />', (string) $form->view()['customView']);

        $req = new CheckboxTestingRequest();
        $req->field = true;
        $req->customValue = true;

        $this->assertEquals('<input name="field" value="1" type="checkbox" checked />', (string) $form->import($req)->view()['field']);
        $this->assertEquals('<input name="customValue" value="on" type="checkbox" checked />', (string) $form->import($req)->view()['customValue']);

        $form = $generated ? $this->generatedForm(ParentTestingRequest::class) : $this->runtimeForm(ParentTestingRequest::class);

        $this->assertEquals('<input name="inner[field]" value="1" type="checkbox" />', (string) $form->view()['inner']['field']);
        $this->assertEquals('<input name="inner[customValue]" value="on" type="checkbox" />', (string) $form->view()['inner']['customValue']);
    }

    public function test_generate_transformer()
    {
        $transformer = new Checkbox();
        $generator = new FormTransformerGenerator($this->registry);

        $this->assertSame('($data["foo"] ?? null) === true ? \'1\' : null', $transformer->generateTransformToHttp($transformer, '$data["foo"] ?? null', $generator));
        $this->assertSame('($data["foo"] ?? null) === true ? \'on\' : null', $transformer->generateTransformToHttp(new Checkbox('on'), '$data["foo"] ?? null', $generator));

        $this->assertSame('is_scalar(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null)) && (string) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 === \'1\'', $transformer->generateTransformFromHttp($transformer, '$data["foo"] ?? null', $generator));
        $this->assertSame('is_scalar(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null)) && (string) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 === \'on\'', $transformer->generateTransformFromHttp(new Checkbox('on'), '$data["foo"] ?? null', $generator));
    }

    public function test_generate_view()
    {
        $transformer = new Checkbox();

        $this->assertSame('new \Quatrevieux\Form\View\FieldView(\'foo\', \'1\', ($__tmp_6fb11202bf82766a881ea3ed253bc52c = $errors["foo"] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6fb11202bf82766a881ea3ed253bc52c : null, [\'class\' => \'my-checkbox\', \'type\' => \'checkbox\', \'checked\' => is_scalar(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null)) && (string) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 === \'1\'])', $transformer->generateFieldViewExpression($transformer, 'foo', ['class' => 'my-checkbox'])('$data["foo"] ?? null', '$errors["foo"] ?? null', null));
        $this->assertSame('new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", \'1\', ($__tmp_6fb11202bf82766a881ea3ed253bc52c = $errors["foo"] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6fb11202bf82766a881ea3ed253bc52c : null, [\'class\' => \'my-checkbox\', \'type\' => \'checkbox\', \'checked\' => is_scalar(($__tmp_cf8d20da9cb97be602abb1ce003a22b3 = $data["foo"] ?? null)) && (string) $__tmp_cf8d20da9cb97be602abb1ce003a22b3 === \'1\'])', $transformer->generateFieldViewExpression($transformer, 'foo', ['class' => 'my-checkbox'])('$data["foo"] ?? null', '$errors["foo"] ?? null', '$rootField'));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CheckboxTestingRequest::class) : $this->runtimeForm(CheckboxTestingRequest::class);
        $view = $form->view();

        $this->assertEquals('<input name="field" value="1" type="checkbox" />', (string) $view['field']);
        $this->assertEquals('<input name="customValue" value="on" type="checkbox" />', (string) $view['customValue']);
        $this->assertEquals('<input name="mustBeChecked" value="1" type="checkbox" />', (string) $view['mustBeChecked']);

        $this->assertNull($view['field']->error);
        $this->assertNull($view['customValue']->error);
        $this->assertNull($view['mustBeChecked']->error);

        $submitted = $form->submit([]);

        $this->assertFalse($submitted->valid());
        $this->assertErrors(['mustBeChecked' => 'You should check this checkbox'], $submitted->errors());
        $this->assertFalse($submitted->value()->field);
        $this->assertFalse($submitted->value()->customValue);
        $this->assertFalse($submitted->value()->mustBeChecked);

        $view = $submitted->view();

        $this->assertEquals('<input name="field" value="1" type="checkbox" />', (string) $view['field']);
        $this->assertEquals('<input name="customValue" value="on" type="checkbox" />', (string) $view['customValue']);
        $this->assertEquals('<input name="mustBeChecked" value="1" type="checkbox" />', (string) $view['mustBeChecked']);

        $this->assertNull($view['field']->error);
        $this->assertNull($view['customValue']->error);
        $this->assertError('You should check this checkbox', $view['mustBeChecked']->error);

        $submitted = $form->submit(['field' => '1', 'mustBeChecked' => '1']);

        $this->assertTrue($submitted->valid());
        $this->assertTrue($submitted->value()->field);
        $this->assertFalse($submitted->value()->customValue);
        $this->assertTrue($submitted->value()->mustBeChecked);

        $view = $submitted->view();

        $this->assertEquals('<input name="field" value="1" type="checkbox" checked />', (string) $view['field']);
        $this->assertEquals('<input name="customValue" value="on" type="checkbox" />', (string) $view['customValue']);
        $this->assertEquals('<input name="mustBeChecked" value="1" type="checkbox" checked />', (string) $view['mustBeChecked']);

        $this->assertNull($view['field']->error);
        $this->assertNull($view['customValue']->error);
        $this->assertNull($view['mustBeChecked']->error);
    }

    public function test_canThrowError()
    {
        $this->assertFalse((new Checkbox())->canThrowError());
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class FieldViewType implements FieldViewAttributesProviderInterface
{
    public function __construct(
        public readonly string $type
    ) {
    }

    public function getAttributes(): array
    {
        return ['type' => $this->type];
    }
}

class CheckboxTestingRequest
{
    #[Checkbox]
    public ?bool $field;

    #[Checkbox(httpValue: 'on')]
    public bool $customValue;

    #[Checkbox, IdenticalTo(true, message: 'You should check this checkbox')]
    public bool $mustBeChecked;

    #[Checkbox, FieldViewType('submit')]
    public ?bool $customView;
}

class ParentTestingRequest
{
    #[Embedded(CheckboxTestingRequest::class)]
    public ?CheckboxTestingRequest $inner;
}
