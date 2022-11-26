<?php

namespace Quatrevieux\Form\Validator\Generator;

use Attribute;
use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\SelfValidatedConstraint;
use Quatrevieux\Form\Validator\FieldError;

class GenericValidatorGeneratorTest extends FormTestCase
{
    public function test_generate()
    {
        $this->assertSame("(\$__constraint_16146ff2d6723a9f78e7f45b3871c5bf = new \Quatrevieux\Form\Validator\Constraint\Length(min: 5, max: NULL, message: 'Invalid length'))->validate(\$__constraint_16146ff2d6723a9f78e7f45b3871c5bf, \$data->foo ?? null, \$data)", (new GenericValidatorGenerator())->generate(new Length(min: 5), '$data->foo ?? null'));
        $this->assertSame("(\$__constraint_63ba69d6fe3ff5f84a29bcaaaeae7448 = new \Quatrevieux\Form\Validator\Generator\MyCustomConstraint(foo: 5))->getValidator(\$this->validatorRegistry)->validate(\$__constraint_63ba69d6fe3ff5f84a29bcaaaeae7448, \$data->foo ?? null, \$data)", (new GenericValidatorGenerator())->generate(new MyCustomConstraint(foo: 5), '$data->foo ?? null'));
    }

    public function test_functional()
    {
        $this->container->set(MyCustomConstraintValidator::class, new MyCustomConstraintValidator());

        $form = $this->generatedForm(TestRequestWithGenericValidator::class);

        $this->assertEquals(['a' => 'My error', 'b' => 'My error 2'], $form->submit(['a' => 3, 'b' => 3])->errors());
        $this->assertEquals(['b' => 'My error 2'], $form->submit(['a' => 2, 'b' => 2])->errors());
        $this->assertTrue($form->submit(['a' => 2, 'b' => 7])->valid());
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyCustomConstraint implements ConstraintInterface
{
    public function __construct(
        public int $foo,
    ) {
    }

    public function getValidator(ConstraintValidatorRegistryInterface $registry): ConstraintValidatorInterface
    {
        return $registry->getValidator(MyCustomConstraintValidator::class);
    }
}

class MyCustomConstraintValidator implements ConstraintValidatorInterface
{
    /**
     * @param MyCustomConstraint $constraint
     */
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if (($constraint->foo % $value) !== 1) {
            return new FieldError('My error');
        }

        return null;
    }
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class MyCustomSelfConstraint extends SelfValidatedConstraint
{
    public function __construct(
        public int $foo,
    ) {
    }

    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        if (($constraint->foo % $value) !== 1) {
            return new FieldError('My error 2');
        }

        return null;
    }
}

class TestRequestWithGenericValidator
{
    #[MyCustomConstraint(5)]
    public int $a;

    #[MyCustomSelfConstraint(8)]
    public int $b;
}
