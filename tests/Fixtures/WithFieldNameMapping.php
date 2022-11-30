<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Transformer\Field\HttpField;

class WithFieldNameMapping
{
    #[HttpField('my_complex_name')]
    public ?string $myComplexName;

    #[HttpField('other')]
    public ?int $otherField;
}
