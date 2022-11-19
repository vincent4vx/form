<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Instantiator\PublicPropertyInstantiator;
use Quatrevieux\Form\Transformer\Field\FieldTransformerRegistryInterface;
use Quatrevieux\Form\Transformer\RuntimeFormTransformer;
use Quatrevieux\Form\Validator\Constraint\NullConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\RuntimeValidator;

class FormTest extends TestCase
{
    public function test_submit_simple_success_should_instantiate_dto()
    {
        $form = new Form(
            new RuntimeFormTransformer(['foo' => [], 'bar' => []], $this->createMock(FieldTransformerRegistryInterface::class)),
            new PublicPropertyInstantiator(SimpleRequest::class),
            new RuntimeValidator(new NullConstraintValidatorRegistry(), []),
        );

        $submitted = $form->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $this->assertTrue($submitted->valid());
        $this->assertSame('aaa', $submitted->value()->foo);
        $this->assertSame('bbb', $submitted->value()->bar);
    }
}
