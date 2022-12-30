<?php

namespace Quatrevieux\Form\Transformer\Field;

use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

class CsvTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CsvTestRequest::class) : $this->runtimeForm(CsvTestRequest::class);

        $this->assertNull($form->submit([])->value()->simple);
        $this->assertNull($form->submit(['simple' => []])->value()->simple);
        $this->assertNull($form->submit([])->value()->withEnclosure);

        $this->assertSame(['foo', 'bar'], $form->submit(['simple' => 'foo,bar'])->value()->simple);
        $this->assertSame(['foo', 'bar'], $form->submit(['withEnclosure' => 'foo;"bar"'])->value()->withEnclosure);
        $this->assertSame(['fo"o', 'bar', 'baz;rab'], $form->submit(['withEnclosure' => '"fo""o";bar;"baz;rab"'])->value()->withEnclosure);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(CsvTestRequest::class) : $this->runtimeForm(CsvTestRequest::class);

        $this->assertNull($form->import(new CsvTestRequest())->httpValue()['simple']);
        $this->assertNull($form->import(new CsvTestRequest())->httpValue()['withEnclosure']);

        $this->assertSame('foo,bar', $form->import(CsvTestRequest::simple(['foo', 'bar']))->httpValue()['simple']);
        $this->assertSame('12,34', $form->import(CsvTestRequest::simple([12, 34]))->httpValue()['simple']);
        $this->assertSame('foo;bar', $form->import(CsvTestRequest::withEnclosure(['foo', 'bar']))->httpValue()['withEnclosure']);
        $this->assertSame('12;34', $form->import(CsvTestRequest::withEnclosure([12, 34]))->httpValue()['withEnclosure']);
        $this->assertSame('foo;"b;ar"', $form->import(CsvTestRequest::withEnclosure(['foo', 'b;ar']))->httpValue()['withEnclosure']);
        $this->assertSame('foo;"b""ar"', $form->import(CsvTestRequest::withEnclosure(['foo', 'b"ar']))->httpValue()['withEnclosure']);
        $this->assertSame('foo;"b'."\n".'ar"', $form->import(CsvTestRequest::withEnclosure(['foo', "b\nar"]))->httpValue()['withEnclosure']);
    }

    public function test_generateTransformFromHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(is_string($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? str_getcsv($__tmp_4e6c78d168de10f915401b0dad567ede, \',\', \'\', \'\') : null)', (new Csv())->generateTransformFromHttp(new Csv(), '$data["foo"]', $generator));
        $this->assertSame('(is_string($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? str_getcsv($__tmp_4e6c78d168de10f915401b0dad567ede, \';\', \'"\', \'\') : null)', (new Csv())->generateTransformFromHttp(new Csv(separator: ';', enclosure: '"'), '$data["foo"]', $generator));
    }

    public function test_generateTransformToHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(is_array($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? implode(\',\', $__tmp_4e6c78d168de10f915401b0dad567ede) : null)', (new Csv())->generateTransformToHttp(new Csv(), '$data["foo"]', $generator));
        $this->assertSame('(is_array($__tmp_4e6c78d168de10f915401b0dad567ede = $data["foo"]) ? \Quatrevieux\Form\Transformer\Field\Csv::toCsv($__tmp_4e6c78d168de10f915401b0dad567ede, \';\', \'"\') : null)', (new Csv())->generateTransformToHttp(new Csv(separator: ';', enclosure: '"'), '$data["foo"]', $generator));
    }
}

class CsvTestRequest
{
    #[Csv]
    public ?array $simple;

    #[Csv(separator: ';', enclosure: '"')]
    public ?array $withEnclosure;

    public static function simple(array $value)
    {
        $t = new self();
        $t->simple = $value;
        return $t;
    }

    public static function withEnclosure(array $value)
    {
        $t = new self();
        $t->withEnclosure = $value;
        return $t;
    }
}
