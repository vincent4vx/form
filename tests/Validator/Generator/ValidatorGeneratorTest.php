<?php

namespace Quatrevieux\Form\Validator\Generator;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\Constraint\SelfValidatedConstraint;
use Quatrevieux\Form\Validator\Constraint\ValidateArray;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\RuntimeValidator;
use Quatrevieux\Form\Validator\ValidatorInterface;

class ValidatorGeneratorTest extends FormTestCase
{
    public function test_generate()
    {
        $generator = new ValidatorGenerator($reg = new DefaultRegistry());
        $code = $generator->generate('TestingValidatorGeneratorValidatorClass', new RuntimeValidator($reg, [
            'foo' => [new Length(min: 3)],
            'bar' => [new ConstraintWithoutGenerator()],
            'baz' => [new ValidateArray([new Length(min: 3)])],
        ]));

        $this->assertSame(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class TestingValidatorGeneratorValidatorClass implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        $translator = $this->registry->getTranslator();
        if (!isset($previousErrors['foo']) && $__error_foo = (is_scalar(($data->foo ?? null)) && (($__len_a3aa3c8caea059c99a14cd36eaceca72 = strlen(($data->foo ?? null))) < 3) ? new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], 'ecdd71f6-fa22-5564-bfc7-7e836dce3378') : null)) {
            $errors['foo'] = $__error_foo->withTranslator($translator);
        }

        if (!isset($previousErrors['bar']) && $__error_bar = (($__constraint_e1771d42830cea409755e777bda75cbb = new \Quatrevieux\Form\Validator\Generator\ConstraintWithoutGenerator())->validate($__constraint_e1771d42830cea409755e777bda75cbb, ($data->bar ?? null), $data))) {
            $errors['bar'] = is_array($__error_bar) ? $__error_bar : $__error_bar->withTranslator($translator);
        }

        if (!isset($previousErrors['baz']) && $__error_baz = (!\is_array($__tmp_fd3c0a5303c59b067ab1079684382b9a = ($data->baz ?? null)) ? null : (function ($value) use($data, $translator) { $valid = true; $errors = []; foreach ($value as $key => $item) { if ($error = is_scalar($item) && (($__len_0f8134fb6038ebcd7155f1de5f067c73 = strlen($item)) < 3) ? new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], 'ecdd71f6-fa22-5564-bfc7-7e836dce3378') : null) { $valid = false; $errors[$key] = $error->withTranslator($translator); } } return $valid ? null : $errors; })($__tmp_fd3c0a5303c59b067ab1079684382b9a))) {
            $errors['baz'] = $__error_baz;
        }

        return $errors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
            , $code
        );

        $this->assertGeneratedClass($code, 'TestingValidatorGeneratorValidatorClass', ValidatorInterface::class);
        $this->assertEmpty((new \TestingValidatorGeneratorValidatorClass(new DefaultRegistry()))->validate((object) ['foo' => 'aaaa', 'bar' => 'aaaa']));
        $this->assertErrors(['foo' => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE)], (new \TestingValidatorGeneratorValidatorClass(new DefaultRegistry()))->validate((object) ['foo' => 'a']));
        $this->assertErrors(['foo' => 'The value is too short. It should have 3 characters or more.'], (new \TestingValidatorGeneratorValidatorClass(new DefaultRegistry()))->validate((object) ['foo' => 'a']));

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
