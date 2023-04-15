<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Validator\Constraint\Choice;

class WithChoiceRequest
{
    #[Choice([
        'The answer' => 42,
        'The beast' => 666,
        'Lost' => 404,
    ])]
    public ?int $value;
}
