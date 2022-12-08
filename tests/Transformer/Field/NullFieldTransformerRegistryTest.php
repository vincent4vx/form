<?php

namespace Quatrevieux\Form\Transformer\Field;

use PHPUnit\Framework\TestCase;

class NullFieldTransformerRegistryTest extends TestCase
{
    public function test_getTransformer()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Cannot use delegated transformer : no container or custom registry defined.');

        $registry = new NullFieldTransformerRegistry();
        $registry->getTransformer('foo');
    }
}
