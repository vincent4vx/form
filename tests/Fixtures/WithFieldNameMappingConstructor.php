<?php

namespace Quatrevieux\Form\Fixtures;

use Quatrevieux\Form\DataMapper\ConstructorDataMapper;
use Quatrevieux\Form\DataMapper\InstantiateWith;
use Quatrevieux\Form\Transformer\Field\HttpField;

#[InstantiateWith(ConstructorDataMapper::class)]
class WithFieldNameMappingConstructor
{
    public function __construct(
        #[HttpField('my_complex_name')]
        public ?string $myComplexName,

        #[HttpField('other')]
        public ?int $otherField,
    ) {}
}
