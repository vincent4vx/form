<?php

namespace Bench;

require_once __DIR__ . '/Fixtures/ComplexeForm.php';

use Bench\Fixtures\ComplexeForm;
use PhpBench\Attributes\AfterClassMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

#[Warmup(2), Iterations(3), Revs(1000)]
#[AfterClassMethods('clearGeneratedDir')]
class EmbeddedBench extends BenchUtils
{
    #[Groups(['valid', 'runtime'])]
    public function benchRuntimeValid()
    {
        $form = $this->runtimeForm(ComplexeForm::class);
        $submitted = $form->submit([
            'pseudo' => 'John',
            'credentials' => [
                'username' => 'j.doe',
                'password' => '$my_s3cr3t',
            ],
            'addresses' => [
                [
                    'street' => '1, rue de la paix',
                    'city' => 'Paris',
                    'zipCode' => '75000',
                    'country' => 'France',
                ],
                [
                    'street' => '224 avenue de la république',
                    'city' => 'Lyon',
                    'zipCode' => '69000',
                    'country' => 'France',
                ],
            ],
        ]);

        if (!$submitted->valid()) {
            throw new \RuntimeException();
        }
    }

    #[Groups(['valid', 'generated'])]
    public function benchGeneratedValid()
    {
        $form = $this->generatedForm(ComplexeForm::class);
        $submitted = $form->submit([
            'pseudo' => 'John',
            'credentials' => [
                'username' => 'j.doe',
                'password' => '$my_s3cr3t',
            ],
            'addresses' => [
                [
                    'street' => '1, rue de la paix',
                    'city' => 'Paris',
                    'zipCode' => '75000',
                    'country' => 'France',
                ],
                [
                    'street' => '224 avenue de la république',
                    'city' => 'Lyon',
                    'zipCode' => '69000',
                    'country' => 'France',
                ],
            ],
        ]);

        if (!$submitted->valid()) {
            throw new \RuntimeException();
        }
    }
}
