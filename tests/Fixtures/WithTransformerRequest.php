<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Transformer\Field\Csv;

class WithTransformerRequest
{
    #[Csv]
    public array $list;
}
