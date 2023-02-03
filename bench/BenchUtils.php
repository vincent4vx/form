<?php

namespace Bench;

use FilesystemIterator;
use Quatrevieux\Form\DefaultFormFactory;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormFactoryInterface;
use Quatrevieux\Form\FormInterface;
use Quatrevieux\Form\Instantiator\GeneratedInstantiatorFactory;
use Quatrevieux\Form\Transformer\GeneratedFormTransformerFactory;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\GeneratedValidatorFactory;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;
use Quatrevieux\Form\Validator\RuntimeValidatorFactory;
use Quatrevieux\Form\View\GeneratedFormViewInstantiatorFactory;
use Quatrevieux\Form\View\RuntimeFormViewInstantiatorFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilder;
use Symfony\Component\Form\FormFactoryInterface as SfFormFactory;
use Symfony\Component\Form\FormInterface as SfForm;
use Symfony\Component\Validator\ValidatorBuilder;

class BenchUtils
{
    private const GENERATED_DIR = __DIR__ . '/_tmp';

    private ?FormFactoryInterface $runtimeFactory = null;
    private ?FormFactoryInterface $generatedFactory = null;
    private ?SfFormFactory $symfonyFactory = null;

    public function runtimeFormFactory(bool $viewSupport = false): FormFactoryInterface
    {
        return DefaultFormFactory::runtime(enabledView: $viewSupport);
    }

    public function runtimeForm(string $dataClass, bool $viewSupport = false): FormInterface
    {
        if (!$this->runtimeFactory) {
            $this->runtimeFactory = $this->runtimeFormFactory($viewSupport);
        }

        return $this->runtimeFactory->create($dataClass);
    }

    public function generatedFormFactory(bool $viewSupport = false): FormFactoryInterface
    {
        return DefaultFormFactory::generated(
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            enabledView: $viewSupport
        );
    }

    public function generatedForm(string $dataClass, bool $viewSupport = false): FormInterface
    {
        if (!$this->generatedFactory) {
            $this->generatedFactory = $this->generatedFormFactory($viewSupport);
        }

        return $this->generatedFactory->create($dataClass);
    }

    public function symfonyForm(string $formClass): SfForm
    {
        if (!$this->symfonyFactory) {
            $this->symfonyFactory = (new FormFactoryBuilder())
                ->addExtension(new ValidatorExtension((new ValidatorBuilder())->getValidator()))
                ->getFormFactory()
            ;
        }

        return $this->symfonyFactory->create($formClass);
    }

    public static function clearGeneratedDir(): void
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
}
