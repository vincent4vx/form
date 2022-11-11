<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Instantiator\PublicPropertyInstantiator;
use Quatrevieux\Form\Validator\Constraint\ContainerConstraintValidatorRegistry;
use Quatrevieux\Form\Validator\RuntimeValidator;

class FormTest extends TestCase
{
    public function test_submit_simple_success_should_instantiate_dto()
    {
        $form = new Form(
            new PublicPropertyInstantiator(SimpleRequest::class),
            new RuntimeValidator(new ContainerConstraintValidatorRegistry(), []),
        );

        $submitted = $form->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $this->assertTrue($submitted->valid());
        $this->assertSame('aaa', $submitted->value()->foo);
        $this->assertSame('bbb', $submitted->value()->bar);
    }
}
