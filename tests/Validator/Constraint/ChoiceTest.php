<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Attribute;
use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DummyTranslator;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\ArrayCast;
use Quatrevieux\Form\Transformer\Field\CastType;
use Quatrevieux\Form\Transformer\Field\FieldTransformerInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\ChoiceView;
use Quatrevieux\Form\View\SelectTemplate;
use Ramsey\Uuid\Uuid;

class ChoiceTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(Choice::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'Choice')->toString());
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_functional_simple(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ChoiceTestRequest::class) : $this->runtimeForm(ChoiceTestRequest::class);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['value' => '45'])->valid());
        $this->assertTrue($form->submit(['value' => '15'])->valid());
        $this->assertTrue($form->submit(['values' => ['15', '23']])->valid());
        $this->assertTrue($form->submit(['notTyped' => 15])->valid());
        $this->assertTrue($form->submit(['floats' => 1.23])->valid());
        $this->assertTrue($form->submit(['floats' => [4.56, 7.89]])->valid());

        $this->assertErrors(['value' => new FieldError('The value is not a valid choice.', ['value' => 56], Choice::CODE)], $form->submit(['value' => '56'])->errors());
        $this->assertErrors(['value' => new FieldError('The value is not a valid choice.', ['value' => 56], Choice::CODE)], $form->submit(['value' => '56'])->errors());
        $this->assertErrors(['values' => [1 => new FieldError('The value is not a valid choice.', ['value' => 20], Choice::CODE)]], $form->submit(['values' => [15, 20]])->errors());
        $this->assertErrors(['notTyped' => new FieldError('The value is not a valid choice.', ['value' => "stdClass Object\n(\n)\n"], Choice::CODE)], $form->submit(['notTyped' => new \stdClass()])->errors());
        $this->assertFalse($form->submit(['notTyped' => 15.0])->valid());
        $this->assertFalse($form->submit(['notTyped' => '15'])->valid());
        $this->assertFalse($form->submit(['floats' => '1.23'])->valid());
        $this->assertFalse($form->submit(['floats' => 1.24])->valid());
        $this->assertFalse($form->submit(['floats' => [4.65, 7.89]])->valid());
        $this->assertFalse($form->submit(['floats' => ['4.56', 7.89]])->valid());
    }

    public function test_generated_code()
    {
        $this->assertGeneratedValidator('($data->foo ?? null) === null ? null : (is_array(($data->foo ?? null)) ? (function ($values) {$errors = [];$choices = [15 => 15, 23 => 23, 45 => 45];foreach ($values as $key => $value) {if (!((is_int($value) || is_string($value)) && (($choices[$value] ?? null) === $value))) {$errors[$key] = new \Quatrevieux\Form\Validator\FieldError(\'The value is not a valid choice.\', [\'value\' => is_scalar($value) || $value instanceof \Stringable ? $value : print_r($value, true)], \'41ac8b62-e143-5644-a3eb-0fbfff5a2064\');}}return $errors ?: null;})(($data->foo ?? null)) : (!((is_int(($data->foo ?? null)) || is_string(($data->foo ?? null))) && (([15 => 15, 23 => 23, 45 => 45][($data->foo ?? null)] ?? null) === ($data->foo ?? null))) ? new \Quatrevieux\Form\Validator\FieldError(\'The value is not a valid choice.\', [\'value\' => is_scalar(($data->foo ?? null)) || ($data->foo ?? null) instanceof \Stringable ? ($data->foo ?? null) : print_r(($data->foo ?? null), true)], \'41ac8b62-e143-5644-a3eb-0fbfff5a2064\') : null))', new Choice([15, 23, 45]));
        $this->assertGeneratedValidator('($data->foo ?? null) === null ? null : (is_array(($data->foo ?? null)) ? (function ($values) {$errors = [];$choices = [1.5, 2.3, 4.5];foreach ($values as $key => $value) {if (!in_array($value, $choices, true)) {$errors[$key] = new \Quatrevieux\Form\Validator\FieldError(\'my error\', [\'value\' => is_scalar($value) || $value instanceof \Stringable ? $value : print_r($value, true)], \'41ac8b62-e143-5644-a3eb-0fbfff5a2064\');}}return $errors ?: null;})(($data->foo ?? null)) : (!in_array(($data->foo ?? null), [1.5, 2.3, 4.5], true) ? new \Quatrevieux\Form\Validator\FieldError(\'my error\', [\'value\' => is_scalar(($data->foo ?? null)) || ($data->foo ?? null) instanceof \Stringable ? ($data->foo ?? null) : print_r(($data->foo ?? null), true)], \'41ac8b62-e143-5644-a3eb-0fbfff5a2064\') : null))', new Choice([1.5, 2.3, 4.5], message: 'my error'));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_view(bool $generated)
    {
        $form = $generated ? $this->generatedForm(ChoiceTestRequest::class) : $this->runtimeForm(ChoiceTestRequest::class);
        $view = $form->view();

        $setTranslator = function (array $choices) {
            foreach ($choices as $choice) {
                $choice->setTranslator(DummyTranslator::instance());
            }

            return $choices;
        };

        $this->assertEquals($setTranslator([
            new ChoiceView(15),
            new ChoiceView(23),
            new ChoiceView(45),
        ]), $view['value']->choices);

        $this->assertEquals($setTranslator([
            new ChoiceView('f'),
            new ChoiceView('n'),
            new ChoiceView('1d'),
        ]), $view['withTransformer']->choices);

        $submitted = $form->submit(['value' => '15', 'withTransformer' => 'n']);
        $view = $submitted->view();

        $this->assertEquals($setTranslator([
            new ChoiceView(15, selected: true),
            new ChoiceView(23),
            new ChoiceView(45),
        ]), $view['value']->choices);

        $this->assertEquals($setTranslator([
            new ChoiceView('f'),
            new ChoiceView('n', selected: true),
            new ChoiceView('1d'),
        ]), $view['withTransformer']->choices);

        $this->assertEquals('<select name="value" ><option value="15" selected>15</option><option value="23" >23</option><option value="45" >45</option></select>', $view['value']->render(SelectTemplate::Select));
        $this->assertEquals('<select name="withLabel" ><option value="15" >Foo</option><option value="23" >Other value</option><option value="45" >Random label</option></select>', $view['withLabel']->render(SelectTemplate::Select));
        $this->assertEquals('<div ><label><input type="radio" name="withLabel" value="15" >Foo</label><label><input type="radio" name="withLabel" value="23" >Other value</label><label><input type="radio" name="withLabel" value="45" >Random label</label><div>', $view['withLabel']->render(SelectTemplate::Radio));
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_view_label_translated(bool $generated)
    {
        $this->configureTranslator('fr', [
            'Foo' => 'Foo FR',
            'Other value' => 'Autre value',
            'Random label' => 'Etiquette aléatoire',
        ]);
        $form = $generated ? $this->generatedForm(ChoiceTestRequest::class) : $this->runtimeForm(ChoiceTestRequest::class);

        $view = $form->view();
        $this->assertEquals('<select name="withLabel" ><option value="15" >Foo FR</option><option value="23" >Autre value</option><option value="45" >Etiquette aléatoire</option></select>', $view['withLabel']->render(SelectTemplate::Select));
    }
}

class ChoiceTestRequest
{
    #[Choice([15, 23, 45])]
    public ?int $value;

    #[Choice([15, 23, 45])]
    #[ArrayCast(CastType::Int)]
    public ?array $values;

    #[Choice([15, 23, 45])]
    public mixed $notTyped;

    #[Choice([1.23, 4.56, 7.89])]
    public mixed $floats;

    #[Choice([15, 23, 45])]
    #[MyTransformer]
    public ?int $withTransformer;

    #[Choice([
        'Foo' => 15,
        'Other value' => 23,
        'Random label' => 45,
    ])]
    public ?int $withLabel;
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyTransformer implements FieldTransformerInterface
{
    public function transformFromHttp(mixed $value): mixed
    {
        return $value ? base_convert($value, 32, 10) : null;
    }

    public function transformToHttp(mixed $value): mixed
    {
        return $value ? base_convert($value, 10, 32) : null;
    }

    public function canThrowError(): bool
    {
        return false;
    }
}