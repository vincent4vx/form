<?php

namespace Quatrevieux\Form;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\DataMapper\PublicPropertyDataMapper;
use Quatrevieux\Form\Transformer\RuntimeFormTransformer;
use Quatrevieux\Form\Validator\RuntimeValidator;
use Quatrevieux\Form\View\RuntimeFormViewInstantiator;

class FormTest extends TestCase
{
    public function test_submit_simple_success_should_instantiate_dto()
    {
        $form = new Form(
            new RuntimeFormTransformer(new DefaultRegistry(), ['foo' => [], 'bar' => []], [], []),
            new PublicPropertyDataMapper(SimpleRequest::class),
            new RuntimeValidator(new DefaultRegistry(), []),
            new RuntimeFormViewInstantiator(new DefaultRegistry(), SimpleRequest::class, [], [], [], []),
        );

        $submitted = $form->submit(['foo' => 'aaa', 'bar' => 'bbb']);

        $this->assertTrue($submitted->valid());
        $this->assertSame('aaa', $submitted->value()->foo);
        $this->assertSame('bbb', $submitted->value()->bar);
    }
}
