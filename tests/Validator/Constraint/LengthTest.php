<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormInterface;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Ramsey\Uuid\Uuid;

class LengthTest extends FormTestCase
{
    private FormInterface $form;
    private FormInterface $generatedForm;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->runtimeForm(LengthTestRequest::class);
        $this->generatedForm = $this->generatedForm(LengthTestRequest::class);
    }

    public function test_code()
    {
        $this->assertSame(Length::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'Length')->toString());
    }

    public function test_missing_min_and_max()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('At least one of parameters "min" or "max" must be set');

        new Length();
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_should_ignore_null_and_non_string(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertTrue($form->submit(['notString' => []])->valid());
        $this->assertTrue($form->submit(['onlyMin' => null])->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_min_len_check(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $result = $form->submit(['onlyMin' => 'a', 'onlyMax' => 'a', 'both' => 'a']);

        $this->assertFalse($result->valid());
        $this->assertErrors([
            'onlyMin' => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE),
            'both' => new FieldError('my error', ['min' => 3, 'max' => 6], Length::CODE),
        ], $result->errors());

        $result = $form->submit(['onlyMin' => 'aaaa', 'onlyMax' => 'aaaa', 'both' => 'aaaaa']);
        $this->assertTrue($result->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_max_len_check(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $result = $form->submit(['onlyMin' => 'aaaaaaaaaaaaaa', 'onlyMax' => 'aaaaaaaaaaaaaa', 'both' => 'aaaaaaaaaaaaaaa']);

        $this->assertFalse($result->valid());
        $this->assertErrors([
            'both' => new FieldError('my error', ['min' => 3, 'max' => 6], Length::CODE),
            'onlyMax' => new FieldError('The value is too long. It should have {{ max }} characters or less.', ['max' => 6], Length::CODE),
        ], $result->errors());

        $result = $form->submit(['onlyMin' => 'aaaa', 'onlyMax' => 'aaaa', 'both' => 'aaaaa']);
        $this->assertTrue($result->valid());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_field_view(bool $generated)
    {
        $form = $generated ? $this->generatedForm : $this->form;

        $this->assertEquals('<input name="onlyMin" value="" minlength="3" />', $form->view()['onlyMin']);
        $this->assertEquals('<input name="onlyMax" value="" maxlength="6" />', $form->view()['onlyMax']);
        $this->assertEquals('<input name="both" value="" minlength="3" maxlength="6" />', $form->view()['both']);
    }

    public function test_generated_code()
    {
        $generator = new ValidatorGenerator(new DefaultRegistry());
        $onlyMin = new Length(min: 3, message: 'my error');
        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && (($__len_a3aa3c8caea059c99a14cd36eaceca72 = strlen(($data->foo ?? null))) < 3) ? new FieldError(\'my error\', [\'min\' => 3], \'ecdd71f6-fa22-5564-bfc7-7e836dce3378\') : null', $onlyMin);

        $onlyMax = new Length(max: 3, message: 'my error');
        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && (($__len_a3aa3c8caea059c99a14cd36eaceca72 = strlen(($data->foo ?? null))) > 3) ? new FieldError(\'my error\', [\'max\' => 3], \'ecdd71f6-fa22-5564-bfc7-7e836dce3378\') : null', $onlyMax);

        $both = new Length(min: 3, max: 6, message: 'my error');
        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && (($__len_a3aa3c8caea059c99a14cd36eaceca72 = strlen(($data->foo ?? null))) < 3 || $__len_a3aa3c8caea059c99a14cd36eaceca72 > 6) ? new FieldError(\'my error\', [\'min\' => 3, \'max\' => 6], \'ecdd71f6-fa22-5564-bfc7-7e836dce3378\') : null', $both);
    }
}

class LengthTestRequest
{
    #[Length(min: 3)]
    public ?string $onlyMin;
    #[Length(max: 6)]
    public ?string $onlyMax;
    #[Length(min: 3, max: 6, message: 'my error')]
    public ?string $both;
    #[Length(max: 6)]
    public ?array $notString;
}
