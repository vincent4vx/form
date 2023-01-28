<?php

namespace Bench\Fixtures;

use Quatrevieux\Form\View\FormView;
use Symfony\Component\Form\FormView as SfFormView;

class SimpleFormTemplate
{
    public static function render(FormView $view): string
    {
        return <<<HTML
<form method="POST" action="/foo">
    {$view['firstName']->type('text')}
    {$view['lastName']->type('text')}
    {$view['age']->type('number')}
</form>
HTML;
    }
}
