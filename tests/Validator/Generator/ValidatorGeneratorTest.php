<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\Constraint\SelfValidatedConstraint;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\RuntimeValidator;
use Quatrevieux\Form\Validator\ValidatorInterface;

class ValidatorGeneratorTest extends FormTestCase
{
    public function test_generate()
    {
        $generator = new ValidatorGenerator($reg = new NullConstraintValidatorRegistry());
        $code = $generator->generate('TestingValidatorGeneratorValidatorClass', new RuntimeValidator($reg, [
            'foo' => [new Length(min: 3)],
            'bar' => [new ConstraintWithoutGenerator()],
        ]));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingValidatorGeneratorValidatorClass implements Quatrevieux\Form\Validator\ValidatorInterface
{
    /**
     * @param T $data
     * @return array<string, FieldError>
     *
     * @todo embedded
     */
    function validate(object $data): array
    {
        $errors = [];
        if ($__error_foo = (is_string(($data->foo ?? null)) && (($__len_a3aa3c8caea059c99a14cd36eaceca72 = strlen(($data->foo ?? null))) < 3) ? new FieldError('Invalid length') : null)) {
            $errors['foo'] = $__error_foo;
        }

        if ($__error_bar = (($__constraint_e1771d42830cea409755e777bda75cbb = new \Quatrevieux\Form\Validator\Generator\ConstraintWithoutGenerator())->getValidator($this->validatorRegistry)->validate($__constraint_e1771d42830cea409755e777bda75cbb, ($data->bar ?? null)))) {
            $errors['bar'] = $__error_bar;
        }

        return $errors;
    }

    public function __construct(private readonly Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface $validatorRegistry)
    {
    }
}

PHP
            , $code
        );

        $this->assertGeneratedClass($code, 'TestingWithConstraintCodeValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingValidatorGeneratorValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['foo' => 'aaaa', 'bar' => 'aaaa']));
        $this->assertEquals(['foo' => new FieldError('Invalid length')], (new \TestingValidatorGeneratorValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['foo' => 'a']));
    }
}

class ConstraintWithoutGenerator extends SelfValidatedConstraint
{
    public function validate(ConstraintInterface $constraint, mixed $value): ?FieldError
    {
        return null;
    }
}
