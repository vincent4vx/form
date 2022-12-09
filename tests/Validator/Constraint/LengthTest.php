<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\FieldError;

class LengthTest extends FormTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->form = $this->runtimeForm(LengthTestRequest::class);
        $this->generatedForm = $this->generatedForm(LengthTestRequest::class);
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
        $this->assertEquals([
            'onlyMin' => new FieldError('Invalid length'),
            'both' => new FieldError('my error'),
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
        $this->assertEquals([
            'both' => new FieldError('my error'),
            'onlyMax' => new FieldError('Invalid length'),
        ], $result->errors());

        $result = $form->submit(['onlyMin' => 'aaaa', 'onlyMax' => 'aaaa', 'both' => 'aaaaa']);
        $this->assertTrue($result->valid());
    }

    public function test_generated_code()
    {
        $onlyMin = new Length(min: 3, message: 'my error');
        $this->assertEquals('is_string(($data->field ?? null)) && (($__len_1444ef036f9b3842b62162dc7f14342b = strlen(($data->field ?? null))) < 3) ? new FieldError(\'my error\') : null', $onlyMin->generate($onlyMin, '($data->field ?? null)'));

        $onlyMax = new Length(max: 3, message: 'my error');
        $this->assertEquals('is_string(($data->field ?? null)) && (($__len_1444ef036f9b3842b62162dc7f14342b = strlen(($data->field ?? null))) > 3) ? new FieldError(\'my error\') : null', $onlyMax->generate($onlyMax, '($data->field ?? null)'));

        $both = new Length(min: 3, max: 6, message: 'my error');
        $this->assertEquals('is_string(($data->field ?? null)) && (($__len_1444ef036f9b3842b62162dc7f14342b = strlen(($data->field ?? null))) < 3 || $__len_1444ef036f9b3842b62162dc7f14342b > 6) ? new FieldError(\'my error\') : null', $both->generate($both, '($data->field ?? null)'));
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
