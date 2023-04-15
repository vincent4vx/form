<?php

namespace Quatrevieux\Form\Validator;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\Fixtures\ConfiguredLengthValidator;
use Quatrevieux\Form\Fixtures\RequiredParametersRequest;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\TestConfig;
use Quatrevieux\Form\Fixtures\WithExternalDependencyConstraintRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Field\TransformationError;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\Constraint\Length;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\Constraint\Required;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

class GeneratedValidatorFactoryTest extends FormTestCase
{
    public function test_create_without_constraints()
    {
        $factory = new GeneratedValidatorFactory(
            factory: new RuntimeValidatorFactory($registry = new DefaultRegistry()),
            generator: new ValidatorGenerator($registry),
            registry: $registry,
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
        return $previousErrors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_SimpleRequestValidatorGeneratorTest.php'));

        $validator = $factory->create(SimpleRequest::class);

        $this->assertEmpty($validator->validate(new SimpleRequest()));
        $this->assertErrors(['foo' => new FieldError('my transformer error')], $validator->validate(new SimpleRequest(), ['foo' => new FieldError('my transformer error')]));
    }

    public function test_create_with_constraints()
    {
        $factory = new GeneratedValidatorFactory(
            factory: new RuntimeValidatorFactory($registry = new DefaultRegistry()),
            generator: new ValidatorGenerator($registry),
            registry: $registry,
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
        $translator = $this->registry->getTranslator();
        if (!isset($previousErrors['foo']) && $__error_foo = (($data->foo ?? null) === null || ($data->foo ?? null) === '' || ($data->foo ?? null) === [] ? new FieldError('This value is required', [], 'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5') : null)) {
            $errors['foo'] = $__error_foo->withTranslator($translator);
        }

        if (!isset($previousErrors['bar']) && $__error_bar = (($data->bar ?? null) === null || ($data->bar ?? null) === '' || ($data->bar ?? null) === [] ? new FieldError('bar must be set', [], 'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5') : null) ?? (is_scalar(($data->bar ?? null)) && (($__len_722af90ac1a42c8c3ad647bfd63cd459 = strlen(($data->bar ?? null))) < 3) ? new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], 'ecdd71f6-fa22-5564-bfc7-7e836dce3378') : null)) {
            $errors['bar'] = $__error_bar->withTranslator($translator);
        }

        return $errors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_RequiredParametersRequestValidatorGeneratorTest.php'));

        $validator = $factory->create(RequiredParametersRequest::class);

        $this->assertErrors([
            'foo' => new FieldError('This value is required', code: Required::CODE),
            'bar' => new FieldError('bar must be set', code: Required::CODE),
        ], $validator->validate(new RequiredParametersRequest()));

        $o = new RequiredParametersRequest();
        $o->foo = 1;
        $o->bar = 'b';

        $this->assertErrors([
            'bar' => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE),
        ], $validator->validate($o));
        
        $this->assertErrors([
            'foo' => new FieldError('transformer error', code: TransformationError::CODE),
            'bar' => new FieldError('The value is too short. It should have {{ min }} characters or more.', ['min' => 3], Length::CODE),
        ], $validator->validate($o, ['foo' => new FieldError('transformer error', code: TransformationError::CODE)]));

        $o->bar = 'aaaa';
        $this->assertEmpty($validator->validate($o));
    }

    public function test_create_with_external_constraint()
    {
        $this->container->set(ConfiguredLengthValidator::class, new ConfiguredLengthValidator(new TestConfig(['foo.length' => 3])));

        $factory = new GeneratedValidatorFactory(
            factory: new RuntimeValidatorFactory($registry = new ContainerRegistry($this->container), null),
            generator: new ValidatorGenerator($registry),
            registry: $registry,
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
        $translator = $this->registry->getTranslator();
        if (!isset($previousErrors['foo']) && $__error_foo = (($data->foo ?? null) === null || ($data->foo ?? null) === '' || ($data->foo ?? null) === [] ? new FieldError('This value is required', [], 'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5') : null) ?? (($__constraint_eead8f4bada4cb985586965f0b2d57c9 = new \Quatrevieux\Form\Fixtures\ConfiguredLength(key: 'foo.length'))->getValidator($this->registry)->validate($__constraint_eead8f4bada4cb985586965f0b2d57c9, ($data->foo ?? null), $data))) {
            $errors['foo'] = is_array($__error_foo) ? $__error_foo : $__error_foo->withTranslator($translator);
        }

        return $errors;
    }

    public function __construct(
        private readonly Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP
        , file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_WithExternalDependencyConstraintRequestValidatorGeneratorTest.php'));

        $validator = $factory->create(WithExternalDependencyConstraintRequest::class);

        $o = new WithExternalDependencyConstraintRequest();
        $o->foo = 'aaaaaaaaa';

        $this->assertErrors([
            'foo' => new FieldError('Invalid length'),
        ], $validator->validate($o));

        $o->foo = 'aaa';

        $this->assertEmpty($validator->validate($o));
    }
}
