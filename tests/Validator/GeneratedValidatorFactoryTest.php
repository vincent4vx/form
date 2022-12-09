<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

class GeneratedValidatorFactoryTest extends FormTestCase
{
    public function test_create_without_constraints()
    {
        $factory = new GeneratedValidatorFactory(
            factory: new RuntimeValidatorFactory($registry = new NullConstraintValidatorRegistry()),
            generator: new ValidatorGenerator($registry),
            validatorRegistry: $registry,
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: fn (string $className): string => str_replace('\\', '_', $className) . 'ValidatorGeneratorTest',
        );

        $this->assertInstanceOf(ValidatorInterface::class, $factory->create(SimpleRequest::class));
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestValidatorGeneratorTest', $factory->create(SimpleRequest::class));

        $this->assertEquals(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class Quatrevieux_Form_Fixtures_SimpleRequestValidatorGeneratorTest implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        return $errors;
    }

    public function __construct(private readonly Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface $validatorRegistry)
    {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_SimpleRequestValidatorGeneratorTest.php'));

        $validator = $factory->create(SimpleRequest::class);

        $this->assertEmpty($validator->validate(new SimpleRequest()));
        $this->assertEquals(['foo' => new FieldError('my transformer error')], $validator->validate(new SimpleRequest(), ['foo' => new FieldError('my transformer error')]));
    }

    public function test_create_with_constraints()
    {
        $factory = new GeneratedValidatorFactory(
            factory: new RuntimeValidatorFactory($registry = new NullConstraintValidatorRegistry()),
            generator: new ValidatorGenerator($registry),
            validatorRegistry: $registry,
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: fn (string $className): string => str_replace('\\', '_', $className) . 'ValidatorGeneratorTest',
        );

        $this->assertInstanceOf(ValidatorInterface::class, $factory->create(RequiredParametersRequest::class));
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_RequiredParametersRequestValidatorGeneratorTest', $factory->create(RequiredParametersRequest::class));

        $this->assertEquals(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class Quatrevieux_Form_Fixtures_RequiredParametersRequestValidatorGeneratorTest implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        if (!isset($previousErrors['foo']) && $__error_foo = (($data->foo ?? null) === null || ($data->foo ?? null) === '' || ($data->foo ?? null) === [] ? new FieldError('This value is required') : null)) {
            $errors['foo'] = $__error_foo;
        }

        if (!isset($previousErrors['bar']) && $__error_bar = (($data->bar ?? null) === null || ($data->bar ?? null) === '' || ($data->bar ?? null) === [] ? new FieldError('bar must be set') : null) ?? (is_string(($data->bar ?? null)) && (($__len_722af90ac1a42c8c3ad647bfd63cd459 = strlen(($data->bar ?? null))) < 3) ? new FieldError('Invalid length') : null)) {
            $errors['bar'] = $__error_bar;
        }

        return $errors;
    }

    public function __construct(private readonly Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface $validatorRegistry)
    {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_RequiredParametersRequestValidatorGeneratorTest.php'));

        $validator = $factory->create(RequiredParametersRequest::class);

        $this->assertEquals([
            'foo' => new FieldError('This value is required'),
            'bar' => new FieldError('bar must be set'),
        ], $validator->validate(new RequiredParametersRequest()));

        $o = new RequiredParametersRequest();
        $o->foo = 1;
        $o->bar = 'b';

        $this->assertEquals([
            'bar' => new FieldError('Invalid length'),
        ], $validator->validate($o));
        
        $this->assertEquals([
            'foo' => new FieldError('transformer error'),
            'bar' => new FieldError('Invalid length'),
        ], $validator->validate($o, ['foo' => new FieldError('transformer error')]));

        $o->bar = 'aaaa';
        $this->assertEmpty($validator->validate($o));
    }

    public function test_create_with_external_constraint()
    {
        $this->container->set(ConfiguredLengthValidator::class, new ConfiguredLengthValidator(new TestConfig(['foo.length' => 3])));

        $factory = new GeneratedValidatorFactory(
            factory: new RuntimeValidatorFactory($registry = new ContainerRegistry($this->container)),
            generator: new ValidatorGenerator($registry),
            validatorRegistry: $registry,
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: fn (string $className): string => str_replace('\\', '_', $className) . 'ValidatorGeneratorTest',
        );

        $this->assertInstanceOf(ValidatorInterface::class, $factory->create(WithExternalDependencyConstraintRequest::class));
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_WithExternalDependencyConstraintRequestValidatorGeneratorTest', $factory->create(WithExternalDependencyConstraintRequest::class));

        $this->assertEquals(<<<'PHP'
<?php

use Quatrevieux\Form\Validator\FieldError;

class Quatrevieux_Form_Fixtures_WithExternalDependencyConstraintRequestValidatorGeneratorTest implements Quatrevieux\Form\Validator\ValidatorInterface
{
    function validate(object $data, array $previousErrors = []): array
    {
        $errors = $previousErrors;
        if (!isset($previousErrors['foo']) && $__error_foo = (($data->foo ?? null) === null || ($data->foo ?? null) === '' || ($data->foo ?? null) === [] ? new FieldError('This value is required') : null) ?? (($__constraint_eead8f4bada4cb985586965f0b2d57c9 = new \Quatrevieux\Form\Fixtures\ConfiguredLength(key: 'foo.length'))->getValidator($this->validatorRegistry)->validate($__constraint_eead8f4bada4cb985586965f0b2d57c9, ($data->foo ?? null), $data))) {
            $errors['foo'] = $__error_foo;
        }

        return $errors;
    }

    public function __construct(private readonly Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface $validatorRegistry)
    {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_WithExternalDependencyConstraintRequestValidatorGeneratorTest.php'));

        $validator = $factory->create(WithExternalDependencyConstraintRequest::class);

        $o = new WithExternalDependencyConstraintRequest();
        $o->foo = 'aaaaaaaaa';

        $this->assertEquals([
            'foo' => new FieldError('Invalid length'),
        ], $validator->validate($o));

        $o->foo = 'aaa';

        $this->assertEmpty($validator->validate($o));
    }
}
