<?php

namespace Bench;

use Bench\Fixtures\SimpleForm;
use Bench\Fixtures\SimpleFormSymfony;
use PhpBench\Attributes\AfterClassMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

#[Warmup(2), Iterations(3), Revs(1000)]
#[AfterClassMethods('clearGeneratedDir')]
class SimpleFormBench extends BenchUtils
{
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
}
