<?php

namespace Quatrevieux\Form\Validator\Constraint;

use Quatrevieux\Form\FormTestCase;
use Ramsey\Uuid\Uuid;

class PasswordStrengthTest extends FormTestCase
{
    public function test_code()
    {
        $this->assertSame(PasswordStrength::CODE, Uuid::uuid5(ConstraintInterface::CODE, 'PasswordStrength')->toString());
    }

    public function test_computeStrength()
    {
        $this->assertSame(0, PasswordStrength::computeStrength(''));
        $this->assertSame(3, PasswordStrength::computeStrength('5'));
        $this->assertSame(4, PasswordStrength::computeStrength('d'));
        $this->assertSame(4, PasswordStrength::computeStrength('D'));
        $this->assertSame(5, PasswordStrength::computeStrength('@'));
        $this->assertSame(6, PasswordStrength::computeStrength('12'));
        $this->assertSame(13, PasswordStrength::computeStrength('1234'));
        $this->assertSame(28, PasswordStrength::computeStrength('azerty'));
        $this->assertSame(34, PasswordStrength::computeStrength('Azerty'));
        $this->assertSame(35, PasswordStrength::computeStrength('@zerty'));
        $this->assertSame(39, PasswordStrength::computeStrength('@zEr1y'));
        $this->assertSame(246, PasswordStrength::computeStrength('my very long passphrase with special chars'));
        $this->assertSame(91, PasswordStrength::computeStrength('sdf0@à-adEcsd'));
    }

    /**
     * @testWith [true]
     *           [false]
     */
    public function test_functional(bool $generated)
    {
        $form = $generated ? $this->generatedForm(FormWithPassword::class) : $this->runtimeForm(FormWithPassword::class);

        $submitted = $form->submit(['password' => '123']);

        $this->assertFalse($submitted->valid());
        $this->assertError('My error message 9 / 40', $submitted->errors()['password']);

        $this->assertTrue($form->submit([])->valid());
        $this->assertTrue($form->submit(['password' => 'my strong password'])->valid());
    }

    public function test_generate()
    {
        $this->assertGeneratedValidator('is_scalar(($data->foo ?? null)) && ($__tmp_6860d3a131ede7646fad73c9ca1acef1 = \Quatrevieux\Form\Validator\Constraint\PasswordStrength::computeStrength((string) ($data->foo ?? null))) < 42 ? new \Quatrevieux\Form\Validator\FieldError(\'The password is too weak\', [\'strength\' => $__tmp_6860d3a131ede7646fad73c9ca1acef1, \'min_strength\' => 42], \'adf637fd-31b6-558c-aa97-8c9522d310ca\') : null', new PasswordStrength(min: 42));
    }
}

class FormWithPassword
{
    #[PasswordStrength(min: 40, message: 'My error message {{ strength }} / {{ min_strength }}')]
    public ?string $password;
}
