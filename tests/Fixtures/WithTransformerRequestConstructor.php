<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\DataMapper\ConstructorDataMapper;
use Quatrevieux\Form\DataMapper\InstantiateWith;
use Quatrevieux\Form\Transformer\Field\Csv;

#[InstantiateWith(ConstructorDataMapper::class)]
class WithTransformerRequestConstructor
{
    public function __construct(
        #[Csv(enclosure: '"')]
        public readonly array $list,
    ) {}
}
