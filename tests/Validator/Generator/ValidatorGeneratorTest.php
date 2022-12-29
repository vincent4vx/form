<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\ContainerRegistry;
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
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        $translator = $this->validatorRegistry->getTranslator();
        if (!isset($previousErrors['foo']) && $__error_foo = (is_scalar(($data->foo ?? null)) && (($__len_a3aa3c8caea059c99a14cd36eaceca72 = strlen(($data->foo ?? null))) < 3) ? new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], 'ecdd71f6-fa22-5564-bfc7-7e836dce3378') : null)) {
            $errors['foo'] = is_array($__error_foo) ? $__error_foo : $__error_foo->withTranslator($translator);
        }

        if (!isset($previousErrors['bar']) && $__error_bar = (($__constraint_e1771d42830cea409755e777bda75cbb = new \Quatrevieux\Form\Validator\Generator\ConstraintWithoutGenerator())->validate($__constraint_e1771d42830cea409755e777bda75cbb, ($data->bar ?? null), $data))) {
            $errors['bar'] = is_array($__error_bar) ? $__error_bar : $__error_bar->withTranslator($translator);
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

        $this->assertGeneratedClass($code, 'TestingValidatorGeneratorValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingValidatorGeneratorValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['foo' => 'aaaa', 'bar' => 'aaaa']));
        $this->assertErrors(['foo' => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE)], (new \TestingValidatorGeneratorValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['foo' => 'a']));
        $this->assertErrors(['foo' => 'The value is too short. It should have 3 characters or more.'], (new \TestingValidatorGeneratorValidatorClass(new NullConstraintValidatorRegistry()))->validate((object) ['foo' => 'a']));

        $this->configureTranslator('fr', [
            'The value is too short. It should have {{ min }} characters or more.' => 'La valeur est trop courte. Elle doit avoir au moins {{ min }} caractères.',
        ]);
        $this->assertErrors(['foo' => 'La valeur est trop courte. Elle doit avoir au moins 3 caractères.'], (new \TestingValidatorGeneratorValidatorClass(new ContainerRegistry($this->container)))->validate((object) ['foo' => 'a']));
    }
}

class ConstraintWithoutGenerator extends SelfValidatedConstraint
{
    public function validate(ConstraintInterface $constraint, mixed $value, object $data): ?FieldError
    {
        return null;
    }
}
