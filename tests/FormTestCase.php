<?php

namespace Quatrevieux\Form;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Transformer\GeneratedFormTransformerFactory;
use Quatrevieux\Form\Validator\Constraint\ContainerConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class FormTestCase extends TestCase
{
    private const GENERATED_DIR = __DIR__ . '/_tmp';

    protected FormFactoryInterface $runtimeFormFactory;
    protected FormFactoryInterface $generatedFormFactory;

    protected function setUp(): void
    {
        $this->runtimeFormFactory = new DefaultFormFactory();

        $savePathResolver = fn (string $class) => self::GENERATED_DIR . '/' . str_replace('\\', '_', $class) . '.php';

        $this->generatedFormFactory = new DefaultFormFactory(
            new GeneratedInstantiatorFactory(savePathResolver: $savePathResolver),
            new GeneratedValidatorFactory(
                factory: new RuntimeValidatorFactory($validatorRegistry = new ContainerConstraintValidatorRegistry()),
                generator: new ValidatorGenerator($validatorRegistry),
                validatorRegistry: $validatorRegistry,
                savePathResolver: $savePathResolver,
            ),
            new GeneratedFormTransformerFactory(
                savePathResolver: $savePathResolver
            )
        );
    }

    protected function tearDown(): void
    {
        //return;
        if (!is_dir(self::GENERATED_DIR)) {
            return;
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(self::GENERATED_DIR, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        /** @var \SplFileInfo $fileinfo */
        foreach($files as $fileinfo)
        {
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
    }
}
