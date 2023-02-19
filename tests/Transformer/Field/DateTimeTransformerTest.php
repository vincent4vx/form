<?php

namespace Quatrevieux\Form\Transformer\Field;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

class DateTimeTransformerTest extends FormTestCase
{
    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformFromHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(DateTimeTransformerTestRequest::class) : $this->runtimeForm(DateTimeTransformerTestRequest::class);

        $this->assertNull($form->submit([])->value()->simple);
        $this->assertNull($form->submit(['simple' => []])->value()->simple);
        $this->assertNull($form->submit(['withTimezone' => []])->value()->withTimezone);

        $this->assertEquals(new DateTimeImmutable('2023-01-25 15:00:00'), $form->submit(['simple' => '2023-01-25T15:00'])->value()->simple);
        $this->assertInstanceOf(DateTimeImmutable::class, $form->submit(['simple' => '2023-01-25T15:00'])->value()->simple);
        $this->assertNull($form->submit(['simple' => 'invalid'])->value()->simple);
        $this->assertErrors(['simple' => 'The given value is not a valid date.'], $form->submit(['simple' => 'invalid'])->errors());

        $this->assertEquals(new DateTimeImmutable('2023-01-25T15:00:00+0500'), $form->submit(['withTimezone' => '2023-01-25T15:00'])->value()->withTimezone);
        $this->assertEquals(new DateTimeZone('Indian/Kerguelen'), $form->submit(['withTimezone' => '2023-01-25T15:00'])->value()->withTimezone->getTimezone());

        $this->assertEquals(new DateTimeImmutable('2023-05-12 00:00:00'), $form->submit(['withFormat' => '12/05/2023'])->value()->withFormat);

        $this->assertEquals(new \DateTime('2023-01-25 15:00:00'), $form->submit(['withClass' => '2023-01-25T15:00'])->value()->withClass);
        $this->assertInstanceOf(\DateTime::class, $form->submit(['withClass' => '2023-01-25T15:00'])->value()->withClass);
    }

    /**
     * @testWith [false]
     *           [true]
     */
    public function test_transformToHttp(bool $generated)
    {
        $form = $generated ? $this->generatedForm(DateTimeTransformerTestRequest::class) : $this->runtimeForm(DateTimeTransformerTestRequest::class);

        $this->assertNull($form->import(new DateTimeTransformerTestRequest())->httpValue()['simple']);

        $this->assertSame('2023-01-25T15:00', $form->import(DateTimeTransformerTestRequest::simple(new DateTimeImmutable('2023-01-25 15:00:00')))->httpValue()['simple']);
        $this->assertSame('2023-01-25T15:00', $form->import(DateTimeTransformerTestRequest::withClass(new DateTime('2023-01-25 15:00:00')))->httpValue()['withClass']);
        $this->assertSame('2023-01-25T20:00', $form->import(DateTimeTransformerTestRequest::withTimezone(new DateTimeImmutable('2023-01-25 15:00:00')))->httpValue()['withTimezone']);
        $this->assertSame('25/01/2023', $form->import(DateTimeTransformerTestRequest::withFormat(new DateTimeImmutable('2023-01-25 15:00:00')))->httpValue()['withFormat']);

        $request = DateTimeTransformerTestRequest::withTimezone($date = new \DateTime('2023-01-25 15:00:00'));
        $before = clone $date;
        $this->assertSame('2023-01-25T20:00', $form->import($request)->httpValue()['withTimezone']);
        $this->assertEquals($before, $date);
        $this->assertEquals($before->getTimezone(), $date->getTimezone());
    }

    public function test_generateTransformFromHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(($__date_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \DateTimeImmutable ? $__date_4e6c78d168de10f915401b0dad567ede : (is_scalar($__date_4e6c78d168de10f915401b0dad567ede) ? (\DateTimeImmutable::createFromFormat(\'!Y-m-d\\\TH:i\', $__date_4e6c78d168de10f915401b0dad567ede, null) ?: throw new \InvalidArgumentException(\'The given value is not a valid date.\')) : null))', (new DateTimeTransformer())->generateTransformFromHttp(new DateTimeTransformer(), '$data["foo"]', $generator));
        $this->assertSame('(($__date_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \DateTimeImmutable ? $__date_4e6c78d168de10f915401b0dad567ede : (is_scalar($__date_4e6c78d168de10f915401b0dad567ede) ? (\DateTimeImmutable::createFromFormat(\'!Y-m-d\\\TH:i\', $__date_4e6c78d168de10f915401b0dad567ede, new \DateTimeZone(\'Europe/Paris\')) ?: throw new \InvalidArgumentException(\'The given value is not a valid date.\')) : null))', (new DateTimeTransformer())->generateTransformFromHttp(new DateTimeTransformer(timezone: 'Europe/Paris'), '$data["foo"]', $generator));
        $this->assertSame('(($__date_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \DateTime ? $__date_4e6c78d168de10f915401b0dad567ede : (is_scalar($__date_4e6c78d168de10f915401b0dad567ede) ? (\DateTime::createFromFormat(\'!Y-m-d\\\TH:i\', $__date_4e6c78d168de10f915401b0dad567ede, null) ?: throw new \InvalidArgumentException(\'The given value is not a valid date.\')) : null))', (new DateTimeTransformer())->generateTransformFromHttp(new DateTimeTransformer(class: DateTime::class), '$data["foo"]', $generator));
    }

    public function test_generateTransformToHttp()
    {
        $generator = new FormTransformerGenerator(new DefaultRegistry());
        $this->assertSame('(($__date_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \DateTimeInterface ? $__date_4e6c78d168de10f915401b0dad567ede->format(\'Y-m-d\\\TH:i\') : null)', (new DateTimeTransformer())->generateTransformToHttp(new DateTimeTransformer(), '$data["foo"]', $generator));
        $this->assertSame('(($__date_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \DateTimeInterface ? ($__date_4e6c78d168de10f915401b0dad567ede instanceof \DateTimeImmutable ? $__date_4e6c78d168de10f915401b0dad567ede : clone $__date_4e6c78d168de10f915401b0dad567ede)->setTimezone(new \DateTimeZone(\'Europe/Paris\'))->format(\'Y-m-d\\\TH:i\') : null)', (new DateTimeTransformer())->generateTransformToHttp(new DateTimeTransformer(timezone: 'Europe/Paris'), '$data["foo"]', $generator));
        $this->assertSame('(($__date_4e6c78d168de10f915401b0dad567ede = $data["foo"]) instanceof \DateTimeInterface ? $__date_4e6c78d168de10f915401b0dad567ede->format(\'Y-m-d\\\TH:i\') : null)', (new DateTimeTransformer())->generateTransformToHttp(new DateTimeTransformer(class: DateTime::class), '$data["foo"]', $generator));
    }
}

class DateTimeTransformerTestRequest
{
    #[DateTimeTransformer]
    public ?DateTimeInterface $simple;

    #[DateTimeTransformer(timezone: 'Indian/Kerguelen')]
    public ?DateTimeInterface $withTimezone;

    #[DateTimeTransformer(format: 'd/m/Y')]
    public ?DateTimeInterface $withFormat;

    #[DateTimeTransformer(class: \DateTime::class)]
    public ?DateTimeInterface $withClass;

    public static function simple(DateTimeInterface $simple): self
    {
        $self = new self();
        $self->simple = $simple;

        return $self;
    }

    public static function withTimezone(DateTimeInterface $date): self
    {
        $self = new self();
        $self->withTimezone = $date;

        return $self;
    }

    public static function withFormat(DateTimeInterface $date): self
    {
        $self = new self();
        $self->withFormat = $date;

        return $self;
    }

    public static function withClass(DateTimeInterface $date): self
    {
        $self = new self();
        $self->withClass = $date;

        return $self;
    }
}
