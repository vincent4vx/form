<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\Transformer\Field\Csv;

class WithTransformerRequestConstructor
{
    public function __construct(
        #[Csv(enclosure: '"')]
        public readonly array $list,
    ) {}
}
