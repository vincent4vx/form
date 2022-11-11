<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithTransformerRequest;
use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Validator\Constraint\ContainerConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;

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

    public function form(string $dataClass): FormInterface
    {
        return $this->runtimeForm($dataClass);
    }
}
