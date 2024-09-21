<?php

namespace Bench;

use Bench\Fixtures\SimpleForm;
use Bench\Fixtures\SimpleFormSymfony;
use Bench\Fixtures\SimpleFormTemplate;
use PhpBench\Attributes\AfterClassMethods;
use PhpBench\Attributes\Groups;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;

#[Warmup(2), Iterations(3), Revs(1000)]
#[AfterClassMethods('clearGeneratedDir')]
class FormViewBench extends BenchUtils
{
    #[Groups(['default', 'runtime'])]
    public function benchRuntimeDefault()
    {
        $form = $this->runtimeForm(SimpleForm::class, true);
        $view = $form->view();

        return SimpleFormTemplate::render($view);
    }

    #[Groups(['default', 'generated'])]
    public function benchGeneratedDefault()
    {
        $form = $this->generatedForm(SimpleForm::class, true);
        $view = $form->view();

        return SimpleFormTemplate::render($view);
    }

    #[Groups(['default', 'symfony'])]
    public function benchSymfonyDefault()
    {
        $form = $this->symfonyForm(SimpleFormSymfony::class);
        return $form->createView(); // render always fails
    }

    #[Groups(['submitted', 'runtime'])]
    public function benchRuntimeSubmitted()
    {
        $form = $this->runtimeForm(SimpleForm::class, true);
        $submitted = $form->submit(['first_name' => 'John', 'last_name' => 'Doe', 'age' => '42']);
        $view = $submitted->view();

        return SimpleFormTemplate::render($view);
    }

    #[Groups(['submitted', 'generated'])]
    public function benchGeneratedSubmitted()
    {
        $form = $this->generatedForm(SimpleForm::class, true);
        $submitted = $form->submit(['first_name' => 'John', 'last_name' => 'Doe', 'age' => '42']);
        $view = $submitted->view();

        return SimpleFormTemplate::render($view);
    }

    #[Groups(['submitted', 'symfony'])]
    public function benchSymfonySubmitted()
    {
        $form = $this->symfonyForm(SimpleFormSymfony::class);
        $submitted = $form->submit(['first_name' => 'John', 'last_name' => 'Doe', 'age' => '42']);
        return $submitted->createView(); // render always fails
    }
}
