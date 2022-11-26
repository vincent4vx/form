<?php

namespace Bench;

use Bench\Fixtures\SimpleForm;
use Bench\Fixtures\SimpleFormSymfony;
use PhpBench\Attributes\AfterClassMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

#[Warmup(2), Iterations(3), Revs(1000)]
#[AfterClassMethods('clearGeneratedDir')]
class SimpleFormBench extends BenchUtils
{
    #[Groups(['valid', 'runtime'])]
    public function benchRuntimeValid()
    {
        $form = $this->runtimeForm(SimpleForm::class);
        $submitted = $form->submit(['firstName' => 'John', 'lastName' => 'Doe', 'age' => '42']);

        if (!$submitted->valid()) {
            throw new \RuntimeException();
        }

        assert($submitted->value()->firstName === 'John');
        assert($submitted->value()->lastName === 'Doe');
        assert($submitted->value()->age === 42);
    }

    #[Groups(['valid', 'generated'])]
    public function benchGeneratedValid()
    {
        $form = $this->generatedForm(SimpleForm::class);
        $submitted = $form->submit(['firstName' => 'John', 'lastName' => 'Doe', 'age' => '42']);

        if (!$submitted->valid()) {
            throw new \RuntimeException();
        }

        assert($submitted->value()->firstName === 'John');
        assert($submitted->value()->lastName === 'Doe');
        assert($submitted->value()->age === 42);
    }

    #[Groups(['valid', 'symfony'])]
    public function benchSymfonyValid()
    {
        $form = $this->symfonyForm(SimpleFormSymfony::class);
        $submitted = $form->submit(['firstName' => 'John', 'lastName' => 'Doe', 'age' => '42']);

        if (!$submitted->isValid()) {
            throw new \RuntimeException();
        }

        $data = $submitted->getData();

        assert($data->firstName === 'John');
        assert($data->lastName === 'Doe');
        assert($data->age === 42);
    }

    #[Groups(['invalid', 'runtime'])]
    public function benchRuntimeInvalid()
    {
        $form = $this->runtimeForm(SimpleForm::class);
        $submitted = $form->submit(['firstName' => 'a', 'lastName' => 'b']);

        assert(!$submitted->valid());
        assert(isset($submitted->errors()['firstName']));
        assert(isset($submitted->errors()['lastName']));
        assert(!isset($submitted->errors()['age']));
    }

    #[Groups(['invalid', 'generated'])]
    public function benchGeneratedInvalid()
    {
        $form = $this->generatedForm(SimpleForm::class);
        $submitted = $form->submit(['firstName' => 'a', 'lastName' => 'b']);

        assert(!$submitted->valid());
        assert(isset($submitted->errors()['firstName']));
        assert(isset($submitted->errors()['lastName']));
        assert(!isset($submitted->errors()['age']));
    }

    #[Groups(['invalid', 'symfony'])]
    public function benchSymfonyInvalid()
    {
        $form = $this->symfonyForm(SimpleFormSymfony::class);
        $submitted = $form->submit(['firstName' => 'a', 'lastName' => 'b']);

        assert(!$submitted->isValid());
        assert(isset($submitted->getErrors()['firstName']));
        assert(isset($submitted->getErrors()['lastName']));
        assert(!isset($submitted->getErrors()['age']));
    }
}
