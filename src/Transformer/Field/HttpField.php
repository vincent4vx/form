<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;

/**
 * Define HTTP field name to use
 * By default, the property name will be used as HTTP field name
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class HttpField
{
    public function __construct(
        public readonly string $name,
    ) {}
}
