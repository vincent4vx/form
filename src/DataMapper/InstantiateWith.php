<?php

namespace Quatrevieux\Form\DataMapper;

use Attribute;

/**
 * Define data mapper class to use for instantiate data object
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class InstantiateWith
{
    public function __construct(
        /**
         * @var class-string<DataMapperInterface>
         */
        public readonly string $dataMapperClassName,
    ) {
    }
}
