<?php

namespace Quatrevieux\Form\View\Generator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Embedded\Embedded;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\View\Provider\FieldViewConfiguration;

class GenericFieldViewProviderGeneratorTest extends TestCase
{
    public function test_generate_not_delegated()
    {
        $generator = new GenericFieldViewProviderGenerator();

        $this->assertSame(
            "(\$__view_963883d3ee3af21f403d92bcfb9c1f52 = new \Quatrevieux\Form\View\Provider\FieldViewConfiguration(type: 'number', id: '_foo', defaultValue: '-1', attributes: ['max' => 100]))->view(\$__view_963883d3ee3af21f403d92bcfb9c1f52, 'foo', null, null, ['required' => true])",
            ($generator->generateFieldViewExpression(
                new FieldViewConfiguration('number', '_foo', '-1', ['max' => 100]),
                'foo',
                ['required' => true]
            ))('null', 'null', null)
        );
    }

    public function test_generate_delegated()
    {
        $generator = new GenericFieldViewProviderGenerator();

        $this->assertSame(
            "(\$__view_1322ee458d3e5dc60025d6fa75ed13f7 = new \Quatrevieux\Form\Embedded\Embedded(class: 'Quatrevieux\\\Form\\\Fixtures\\\SimpleRequest'))->getViewProvider(\$this->registry)->view(\$__view_1322ee458d3e5dc60025d6fa75ed13f7, 'foo', null, null, ['required' => true])",
            ($generator->generateFieldViewExpression(
                new Embedded(SimpleRequest::class),
                'foo',
                ['required' => true]
            ))('null', 'null', null)
        );
    }
}
