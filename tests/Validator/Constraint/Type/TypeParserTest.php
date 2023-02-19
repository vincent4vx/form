<?php

namespace Quatrevieux\Form\Validator\Constraint\Type;

use PHPUnit\Framework\TestCase;

class TypeParserTest extends TestCase
{
    public function test_parse(): void
    {
        $parser = new TypeParser();

        $this->assertEquals(new IntersectionType([new ClassType('stdClass'), new ClassType('DateTime')]), $parser->parse('stdClass&DateTime'));
        $this->assertEquals(new UnionType([PrimitiveType::Object, new ClassType('DateTime')]), $parser->parse('object|DateTime'));
        $this->assertEquals(PrimitiveType::Int, $parser->parse('int'));
        $this->assertEquals(new UnionType([new IntersectionType([new ClassType('Foo'), new ClassType('Bar')]), PrimitiveType::Float]), $parser->parse('Foo&Bar|float'));
        $this->assertEquals(new UnionType([new IntersectionType([new ClassType('Foo'), new ClassType('Bar')]), PrimitiveType::Float]), $parser->parse('(Foo&Bar)|float'));
    }
}
