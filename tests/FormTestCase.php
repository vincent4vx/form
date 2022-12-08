<?php

namespace Quatrevieux\Form;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Transformer\GeneratedFormTransformerFactory;
use Quatrevieux\Form\Transformer\RuntimeFormTransformerFactory;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FormTestCase extends TestCase
{
    protected const GENERATED_DIR = __DIR__ . '/_tmp';

    protected FormFactoryInterface $runtimeFormFactory;
    protected FormFactoryInterface $generatedFormFactory;
    protected ArrayContainer $container;

    protected function setUp(): void
    {
        $this->container = new ArrayContainer();
        $registry = new ContainerRegistry($this->container);

        $this->runtimeFormFactory = DefaultFormFactory::runtime($this->container);

        $savePathResolver = Functions::savePathResolver(self::GENERATED_DIR);

        $this->generatedFormFactory = new DefaultFormFactory(
            new GeneratedInstantiatorFactory(savePathResolver: $savePathResolver),
            new GeneratedValidatorFactory(
                factory: new RuntimeValidatorFactory($registry),
                generator: new ValidatorGenerator($registry),
                validatorRegistry: $registry,
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

    public function eval(string $code): void
    {
        eval(str_replace('<?php', '', $code));
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
        return isset($this->services);
    }

    public function set(string $id, $service): void
    {
        $this->services[$id] = $service;
    }
}
