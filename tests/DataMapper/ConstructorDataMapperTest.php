<?php

namespace Quatrevieux\Form\DataMapper;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\DataMapper\Generator\DataMapperGenerator;
use Quatrevieux\Form\DefaultRegistry;
use Quatrevieux\Form\Fixtures\EmbeddedFormConstructor;
use Quatrevieux\Form\Fixtures\RequestWithDefaultValueConstructor;
use Quatrevieux\Form\Fixtures\RequiredParametersRequestConstructor;
use Quatrevieux\Form\Fixtures\SimpleRequestConstructor;
use Quatrevieux\Form\Fixtures\WithEmbeddedConstructor;
use Quatrevieux\Form\Validator\Constraint\Required;
use stdClass;

class ConstructorDataMapperTest extends TestCase
{
    public function test_empty(): void
    {
        $mapper = new ConstructorDataMapper(stdClass::class, new DefaultRegistry());

        $dto = $mapper->toDataObject([]);

        $this->assertSame(stdClass::class, $mapper->className());
        $this->assertEquals(new stdClass(), $dto->dto);
        $this->assertSame([], $dto->errors);
        $this->assertSame([], $mapper->toArray($dto->dto));
    }

    public function test_nullable_parameters(): void
    {
        $mapper = new ConstructorDataMapper(SimpleRequestConstructor::class, new DefaultRegistry());

        $empty = $mapper->toDataObject([]);

        $this->assertSame([], $empty->errors);
        $this->assertInstanceOf(SimpleRequestConstructor::class, $empty->dto);
        $this->assertSame(null, $empty->dto->foo);
        $this->assertSame(null, $empty->dto->bar);

        $filled = $mapper->toDataObject(['foo' => 'abc', 'bar' => 'def']);

        $this->assertSame([], $filled->errors);
        $this->assertSame('abc', $filled->dto->foo);
        $this->assertSame('def', $filled->dto->bar);
        $this->assertSame(['foo' => 'abc', 'bar' => 'def'], $mapper->toArray($filled->dto));
    }

    public function test_default_values(): void
    {
        $mapper = new ConstructorDataMapper(RequestWithDefaultValueConstructor::class, new DefaultRegistry());

        $dto = $mapper->toDataObject([]);

        $this->assertSame([], $dto->errors);
        $this->assertSame(42, $dto->dto->foo);
        $this->assertSame('???', $dto->dto->bar);
    }

    public function test_required_parameters(): void
    {
        $mapper = new ConstructorDataMapper(RequiredParametersRequestConstructor::class, new DefaultRegistry());

        $dto = $mapper->toDataObject([]);

        $this->assertInstanceOf(RequiredParametersRequestConstructor::class, $dto->dto);
        $this->assertSame(0, $dto->dto->foo);
        $this->assertSame('', $dto->dto->bar);

        $this->assertSame(['foo', 'bar'], array_keys($dto->errors));
        $this->assertSame(Required::CODE, $dto->errors['foo']->code);
        $this->assertSame(Required::CODE, $dto->errors['bar']->code);
        $this->assertSame('This value is required', $dto->errors['foo']->message);
        $this->assertSame('bar must be set', $dto->errors['bar']->message);

        $filled = $mapper->toDataObject(['foo' => 12, 'bar' => 'value']);

        $this->assertSame([], $filled->errors);
        $this->assertSame(12, $filled->dto->foo);
        $this->assertSame('value', $filled->dto->bar);
    }

    public function test_generate_to_array_code(): void
    {
        $mapper = new ConstructorDataMapper(SimpleRequestConstructor::class, new DefaultRegistry());

        $this->assertSame('return get_object_vars($data);', $mapper->generateToArray($mapper));
    }

    public function test_generate_to_data_object_code(): void
    {
        $mapper = new ConstructorDataMapper(RequiredParametersRequestConstructor::class, new DefaultRegistry());

        $this->assertSame(<<<'PHP'
    $errors = [];
    $dto = new \Quatrevieux\Form\Fixtures\RequiredParametersRequestConstructor(foo: $fields['foo'] ?? 0, bar: $fields['bar'] ?? '');

    foreach (['foo' => 'This value is required', 'bar' => 'bar must be set'] as $field => $message) {
        if (!isset($fields[$field])) {
            $errors[$field] = new \Quatrevieux\Form\Validator\FieldError($message, [], 'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5', $this->registry->getTranslator());
        }
    }

    return new \Quatrevieux\Form\DataMapper\DataMapperResult($dto, $errors);
PHP, $mapper->generateToDataObject($mapper));
    }

    public function test_generated_mapper_code_and_runtime_behavior(): void
    {
        $generator = new DataMapperGenerator();
        $mapper = new ConstructorDataMapper(SimpleRequestConstructor::class, new DefaultRegistry());
        $code = $generator->generate('GeneratedSimpleRequestConstructorDataMapper', $mapper);

        $this->assertNotNull($code);
        $this->assertStringContainsString('class GeneratedSimpleRequestConstructorDataMapper implements Quatrevieux\\Form\\DataMapper\\DataMapperInterface', $code);
        $this->assertStringContainsString('return Quatrevieux\\Form\\Fixtures\\SimpleRequestConstructor::class;', $code);
        $this->assertStringContainsString('return get_object_vars($data);', $code);

        eval(str_replace('<?php', '', $code));

        /** @var DataMapperInterface $generatedMapper */
        $generatedMapper = new \GeneratedSimpleRequestConstructorDataMapper(new DefaultRegistry());
        $dto = $generatedMapper->toDataObject(['foo' => 'gen-foo', 'bar' => 'gen-bar']);

        $this->assertSame([], $dto->errors);
        $this->assertSame('gen-foo', $dto->dto->foo);
        $this->assertSame('gen-bar', $dto->dto->bar);
        $this->assertSame(['foo' => 'gen-foo', 'bar' => 'gen-bar'], $generatedMapper->toArray($dto->dto));
    }

    public function test_with_embedded()
    {
        $mapper = new ConstructorDataMapper(WithEmbeddedConstructor::class, new DefaultRegistry());

        $dto = $mapper->toDataObject([]);

        $this->assertSame(WithEmbeddedConstructor::class, $mapper->className());
        $this->assertInstanceOf(WithEmbeddedConstructor::class, $dto->dto);

        $this->assertSame('', $dto->dto->foo);
        $this->assertSame('', $dto->dto->bar);
        $this->assertInstanceOf(EmbeddedFormConstructor::class, $dto->dto->embedded);
        $this->assertFalse(isset($dto->dto->embedded->baz));
        $this->assertFalse(isset($dto->dto->embedded->rab));

        $this->assertCount(3, $dto->errors);
        $this->assertEquals('This value is required', (string) $dto->errors['foo']);
        $this->assertEquals('This value is required', (string) $dto->errors['bar']);
        $this->assertEquals('This value is required', (string) $dto->errors['embedded']);


        $dto = $mapper->toDataObject([
            'foo' => 'azerty',
            'bar' => 'uiop',
            'embedded' => new EmbeddedFormConstructor(
                baz: 'qsdfgh',
                rab: 'jklm',
            ),
        ]);

        $this->assertEquals(new WithEmbeddedConstructor(
            foo: 'azerty',
            bar: 'uiop',
            embedded: new EmbeddedFormConstructor(
                baz: 'qsdfgh',
                rab: 'jklm',
            )
        ), $dto->dto);

        $this->assertEmpty($dto->errors);

        $this->assertSame(
            <<<'PHP'
                $errors = [];
                $dto = new \Quatrevieux\Form\Fixtures\WithEmbeddedConstructor(foo: $fields['foo'] ?? '', bar: $fields['bar'] ?? '', embedded: $fields['embedded'] ?? (new \ReflectionClass('Quatrevieux\\Form\\Fixtures\\EmbeddedFormConstructor'))->newInstanceWithoutConstructor());

                foreach (['foo' => 'This value is required', 'bar' => 'This value is required', 'embedded' => 'This value is required'] as $field => $message) {
                    if (!isset($fields[$field])) {
                        $errors[$field] = new \Quatrevieux\Form\Validator\FieldError($message, [], 'b1ac3a70-06db-5cd6-8f0e-8e6b98b3fcb5', $this->registry->getTranslator());
                    }
                }

                return new \Quatrevieux\Form\DataMapper\DataMapperResult($dto, $errors);
            PHP,
            $mapper->generateToDataObject($mapper)
        );
    }
}
