<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\EqualsWith;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;

class RuntimeValidatorTest extends FormTestCase
{
    public function test_validate_no_constraints()
    {
        $validator = new RuntimeValidator(new NullConstraintValidatorRegistry(), []);
        $o = (object) ['foo' => 'bar'];

        $this->assertSame([], $validator->validate($o));
        $this->assertEquals(['foo' => new FieldError('my transformer error')], $validator->validate($o, ['foo' => new FieldError('my transformer error')]));
    }

    public function test_validate_single_constraint()
    {
        $validator = new RuntimeValidator(new NullConstraintValidatorRegistry(), [
            'foo' => [new Length(min: 3)]
        ]);

        $this->assertSame([], $validator->validate((object) ['foo' => 'bar']));
        $this->assertEquals(['foo' => 'Invalid length'], $validator->validate((object) ['foo' => 'ba']));
        $this->assertEquals(['foo' => new FieldError('my transformer error')], $validator->validate((object) ['foo' => 'ba'], ['foo' => new FieldError('my transformer error')]));
    }

    public function test_validate_should_stop_at_first_field_violation()
    {
        $validator = new RuntimeValidator(new NullConstraintValidatorRegistry(), [
            'foo' => [new Length(min: 3), new Length(max: 5)],
            'bar' => [new EqualsWith('foo')],
        ]);

        $this->assertEquals([
            'foo' => 'Invalid length',
            'bar' => 'Two fields are different',
        ], $validator->validate((object) ['foo' => 'ba', 'bar' => 'aaa']));
    }

    public function test_validate_success()
    {
        $validator = new RuntimeValidator(new NullConstraintValidatorRegistry(), [
            'foo' => [new Length(min: 3), new Length(max: 5)],
            'bar' => [new EqualsWith('foo')],
        ]);

        $this->assertSame([], $validator->validate((object) ['foo' => 'bar', 'bar' => 'bar']));
    }
}
