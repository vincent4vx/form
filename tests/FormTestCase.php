<?php

namespace Quatrevieux\Form;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Transformer\GeneratedFormTransformerFactory;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\Constraint\ConstraintInterface;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\Generator\ConstraintValidatorGeneratorInterface;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorTrait;

class FormTestCase extends TestCase
{
    protected const GENERATED_DIR = __DIR__ . '/_tmp';

    protected FormFactoryInterface $runtimeFormFactory;
    protected FormFactoryInterface $generatedFormFactory;
    protected ArrayContainer $container;
    protected ArrayTranslator $translator;

    protected function setUp(): void
    {
        $this->translator = new ArrayTranslator();
        $this->container = new ArrayContainer();
        $registry = new ContainerRegistry($this->container);

        $this->runtimeFormFactory = DefaultFormFactory::runtime($registry);

        $savePathResolver = Functions::savePathResolver(self::GENERATED_DIR);

        $this->generatedFormFactory = new DefaultFormFactory(
            new GeneratedInstantiatorFactory(savePathResolver: $savePathResolver),
            new GeneratedValidatorFactory(
                factory: new RuntimeValidatorFactory($registry),
                generator: new ValidatorGenerator($registry),
                registry: $registry,
                savePathResolver: $savePathResolver,
            ),
            new GeneratedFormTransformerFactory(
                registry: $registry,
                savePathResolver: $savePathResolver
            )
        );
    }

    protected function tearDown(): void
    {
        if (!is_dir(self::GENERATED_DIR)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::GENERATED_DIR, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $fileinfo */
        foreach($files as $fileinfo) {
            if ($fileinfo->isDir()) {
                rmdir($fileinfo->getRealPath());
            } else {
                unlink($fileinfo->getRealPath());
            }
        }

        if (is_dir(self::GENERATED_DIR)) {
            rmdir(self::GENERATED_DIR);
        }
    }

    public function runtimeForm(string $dataClass): FormInterface
    {
        return $this->runtimeFormFactory->create($dataClass);
    }

    public function generatedForm(string $dataClass): FormInterface
    {
        // Ensure that generated classes will be used
        $this->generatedFormFactory->create($dataClass);

        return $this->generatedFormFactory->create($dataClass);
    }

    public function assertFormIsGenerated(string $dataClass)
    {
        $baseName = self::GENERATED_DIR . '/' . str_replace('\\', '_', $dataClass);

        $this->assertFileExists($baseName . 'Instantiator.php');
        $this->assertFileExists($baseName . 'Validator.php');
        $this->assertFileExists($baseName . 'Transformer.php');
    }

    public function assertGeneratedClass(string $code, string $className, array|string $interfaces = []): void
    {
        $this->eval($code);

        $this->assertTrue(class_exists($className, false));

        foreach ((array) $interfaces as $interface) {
            $this->assertTrue(is_subclass_of($className, $interface));
        }
    }

    public function assertError(string|FieldError $expected, FieldError $actual)
    {
        if (is_string($expected)) {
            $this->assertEquals($expected, (string) $actual);
            return;
        }

        $expected = $expected->withTranslator((new \ReflectionProperty(FieldError::class, 'translator'))->getValue($actual));

        $this->assertEquals($expected, $actual);
    }

    public function assertErrors(array $expected, array $actual)
    {
        $normalizedActual = $actual;
        $normalizedExpected = $expected;

        foreach ($expected as $key => $value) {
            $actualValue = $actual[$key] ?? null;

            if (!$actualValue) {
                continue;
            }

            if (is_string($value)) {
                $normalizedActual[$key] = (string) $actualValue;
            } else {
                $normalizedExpected[$key] = $value->withTranslator((new \ReflectionProperty(FieldError::class, 'translator'))->getValue($actualValue));
            }
        }

        $this->assertEquals($normalizedExpected, $normalizedActual);
    }

    public function assertGeneratedValidator(string $expected, ConstraintInterface $constraint)
    {
        $registry = new ContainerRegistry($this->container);

        if ($constraint instanceof ConstraintValidatorGeneratorInterface) {
            $this->assertEquals($expected, $constraint->generate($constraint, new ValidatorGenerator($registry))->generate('($data->foo ?? null)'));
        } else {
            $this->assertEquals($expected, $constraint->getValidator($registry)->generate($constraint, new ValidatorGenerator($registry))->generate('($data->foo ?? null)'));
        }
    }

    public function eval(string $code): void
    {
        eval(str_replace('<?php', '', $code));
    }

    protected function configureTranslator(string $locale, array $translations = []): void
    {
        $this->translator->setLocale($locale);

        foreach ($translations as $key => $translation) {
            $this->translator->add($locale, $key, $translation);
        }

        $this->container->set(TranslatorInterface::class, $this->translator);
    }
}

class ArrayContainer implements ContainerInterface
{
    public array $services = [];

    public function get(string $id)
    {
        return $this->services[$id];
    }

    public function has(string $id): bool
    {
        return isset($this->services[$id]);
    }

    public function set(string $id, $service): void
    {
        $this->services[$id] = $service;
    }
}

class ArrayTranslator implements TranslatorInterface, LocaleAwareInterface
{
    use TranslatorTrait {
        trans as private _trans;
    }

    public array $translations = [];

    public function add(string $locale, string $id, string $translation): void
    {
        $this->translations[$locale][$id] = $translation;
    }

    public function trans(?string $id, array $parameters = [], string $domain = null, string $locale = null): string
    {
        $locale = $locale ?? $this->getLocale();

        return $this->_trans($this->translations[$locale][$id] ?? $id, $parameters, $domain, $locale);
    }
}
