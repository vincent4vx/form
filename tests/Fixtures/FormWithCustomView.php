<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\View\Provider\FieldViewConfiguration;

class FormWithCustomView
{
    #[FieldViewConfiguration(type: 'number', id: 'form_count', attributes: ['min' => 0, 'max' => 100])]
    public ?int $count;

    #[FieldViewConfiguration(type: 'text', id: 'form_name', defaultValue: 'example')]
    public ?string $name;
}
