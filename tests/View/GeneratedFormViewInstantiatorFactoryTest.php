<?php

namespace Quatrevieux\Form\View;

use Quatrevieux\Form\ContainerRegistry;
use Quatrevieux\Form\Fixtures\FormWithCustomView;
use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\Fixtures\WithFieldNameMapping;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\FieldError;

class GeneratedFormViewInstantiatorFactoryTest extends FormTestCase
{
    public function test_generate_simple()
    {
        $factory = new GeneratedFormViewInstantiatorFactory(
            registry: new ContainerRegistry($this->container),
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: Functions::classNameResolver('ViewGeneratorTesting'),
        );

        $instantiator = $factory->create(SimpleRequest::class);

        $this->assertSame(<<<'PHP'
<?php

class Quatrevieux_Form_Fixtures_SimpleRequestViewGeneratorTesting implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, [])], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, [])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', null, null, []), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', null, null, [])], []) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", null, null, []), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", null, null, [])], []);
    }

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP,
    file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_SimpleRequestViewGeneratorTesting.php')
);

        $this->assertInstanceOf(FormViewInstantiatorInterface::class, $instantiator);
        $this->assertInstanceOf('Quatrevieux_Form_Fixtures_SimpleRequestViewGeneratorTesting', $instantiator);

        $this->assertEquals(new FormView([
            'foo' => new FieldView('foo', null, null),
            'bar' => new FieldView('bar', null, null),
        ], []), $instantiator->default());

        $this->assertEquals(new FormView([
            'foo' => new FieldView('parent[foo]', null, null),
            'bar' => new FieldView('parent[bar]', null, null),
        ], []), $instantiator->default('parent'));

        $this->assertEquals(new FormView([
            'foo' => new FieldView('foo', null, null),
            'bar' => new FieldView('bar', null, null),
        ], []), $instantiator->submitted([], []));

        $this->assertEquals(new FormView([
            'foo' => new FieldView('parent[foo]', null, null),
            'bar' => new FieldView('parent[bar]', null, null),
        ], []), $instantiator->submitted([], [], 'parent'));

        $this->assertEquals(new FormView([
            'foo' => new FieldView('foo', 'abcd', null),
            'bar' => new FieldView('bar', null, null),
        ], ['foo' => 'abcd']), $instantiator->submitted(['foo' => 'abcd'], []));

        $this->assertEquals(new FormView([
            'foo' => new FieldView('parent[foo]', 'abcd', null),
            'bar' => new FieldView('parent[bar]', null, null),
        ], ['foo' => 'abcd']), $instantiator->submitted(['foo' => 'abcd'], [], 'parent'));

        $this->assertEquals(new FormView([
            'foo' => new FieldView('parent[foo]', 'abcd', null),
            'bar' => new FieldView('parent[bar]', null, new FieldError('my error')),
        ], ['foo' => 'abcd']), $instantiator->submitted(['foo' => 'abcd'], ['bar' => new FieldError('my error')], 'parent'));

        $this->assertEquals(new FormView([
            'foo' => new FieldView('foo', 'abcd', null),
            'bar' => new FieldView('bar', null, new FieldError('my error')),
        ], ['foo' => 'abcd']), $instantiator->submitted(['foo' => 'abcd'], ['bar' => new FieldError('my error')]));
    }

    public function test_generate_with_field_mapping()
    {
        $factory = new GeneratedFormViewInstantiatorFactory(
            registry: new ContainerRegistry($this->container),
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: Functions::classNameResolver('ViewGeneratorTesting'),
        );

        $instantiator = $factory->create(WithFieldNameMapping::class);

        $this->assertSame(<<<'PHP'
<?php

class Quatrevieux_Form_Fixtures_WithFieldNameMappingViewGeneratorTesting implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['myComplexName' => new \Quatrevieux\Form\View\FieldView('my_complex_name', $value['my_complex_name'] ?? null, ($__tmp_6646ce58d572c7d5640134a33c951f4a = $errors['myComplexName'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6646ce58d572c7d5640134a33c951f4a : null, []), 'otherField' => new \Quatrevieux\Form\View\FieldView('other', $value['other'] ?? null, ($__tmp_989e5265fe2eb9b109bf3a31e4316669 = $errors['otherField'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_989e5265fe2eb9b109bf3a31e4316669 : null, [])], $value) :  new \Quatrevieux\Form\View\FormView(['myComplexName' => new \Quatrevieux\Form\View\FieldView("{$rootField}[my_complex_name]", $value['my_complex_name'] ?? null, ($__tmp_6646ce58d572c7d5640134a33c951f4a = $errors['myComplexName'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6646ce58d572c7d5640134a33c951f4a : null, []), 'otherField' => new \Quatrevieux\Form\View\FieldView("{$rootField}[other]", $value['other'] ?? null, ($__tmp_989e5265fe2eb9b109bf3a31e4316669 = $errors['otherField'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_989e5265fe2eb9b109bf3a31e4316669 : null, [])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['myComplexName' => new \Quatrevieux\Form\View\FieldView('my_complex_name', null, null, []), 'otherField' => new \Quatrevieux\Form\View\FieldView('other', null, null, [])], []) :  new \Quatrevieux\Form\View\FormView(['myComplexName' => new \Quatrevieux\Form\View\FieldView("{$rootField}[my_complex_name]", null, null, []), 'otherField' => new \Quatrevieux\Form\View\FieldView("{$rootField}[other]", null, null, [])], []);
    }

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP,
            file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_WithFieldNameMappingViewGeneratorTesting.php')
        );

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('my_complex_name', null, null),
            'otherField' => new FieldView('other', null, null),
        ], []), $instantiator->default());

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('parent[my_complex_name]', null, null),
            'otherField' => new FieldView('parent[other]', null, null),
        ], []), $instantiator->default('parent'));

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('my_complex_name', null, null),
            'otherField' => new FieldView('other', null, null),
        ], []), $instantiator->submitted([], []));

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('parent[my_complex_name]', null, null),
            'otherField' => new FieldView('parent[other]', null, null),
        ], []), $instantiator->submitted([], [], 'parent'));

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('my_complex_name', 'abcd', null),
            'otherField' => new FieldView('other', null, null),
        ], ['my_complex_name' => 'abcd']), $instantiator->submitted(['my_complex_name' => 'abcd'], []));

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('parent[my_complex_name]', 'abcd', null),
            'otherField' => new FieldView('parent[other]', null, null),
        ], ['my_complex_name' => 'abcd']), $instantiator->submitted(['my_complex_name' => 'abcd'], [], 'parent'));

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('parent[my_complex_name]', 'abcd', null),
            'otherField' => new FieldView('parent[other]', null, new FieldError('my error')),
        ], ['my_complex_name' => 'abcd']), $instantiator->submitted(['my_complex_name' => 'abcd'], ['otherField' => new FieldError('my error')], 'parent'));

        $this->assertEquals(new FormView([
            'myComplexName' => new FieldView('my_complex_name', 'abcd', null),
            'otherField' => new FieldView('other', null, new FieldError('my error')),
        ], ['my_complex_name' => 'abcd']), $instantiator->submitted(['my_complex_name' => 'abcd'], ['otherField' => new FieldError('my error')]));
    }

    public function test_generate_with_custom_view()
    {
        $factory = new GeneratedFormViewInstantiatorFactory(
            registry: new ContainerRegistry($this->container),
            savePathResolver: Functions::savePathResolver(self::GENERATED_DIR),
            classNameResolver: Functions::classNameResolver('ViewGeneratorTesting'),
        );

        $instantiator = $factory->create(FormWithCustomView::class);

        $this->assertSame(<<<'PHP'
<?php

class Quatrevieux_Form_Fixtures_FormWithCustomViewViewGeneratorTesting implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['count' => new \Quatrevieux\Form\View\FieldView('count', $value['count'] ?? null, ($__tmp_5df2c6c85ffea0dcf7dce879c4143ed5 = $errors['count'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_5df2c6c85ffea0dcf7dce879c4143ed5 : null, ['min' => 0, 'max' => 100, 'id' => 'form_count', 'type' => 'number']), 'name' => new \Quatrevieux\Form\View\FieldView('name', $value['name'] ?? null ?? 'example', ($__tmp_e32b6614c8f91a4bbb98de16ed0af4b5 = $errors['name'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_e32b6614c8f91a4bbb98de16ed0af4b5 : null, ['id' => 'form_name', 'type' => 'text'])], $value) :  new \Quatrevieux\Form\View\FormView(['count' => new \Quatrevieux\Form\View\FieldView("{$rootField}[count]", $value['count'] ?? null, ($__tmp_5df2c6c85ffea0dcf7dce879c4143ed5 = $errors['count'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_5df2c6c85ffea0dcf7dce879c4143ed5 : null, ['min' => 0, 'max' => 100, 'id' => 'form_count', 'type' => 'number']), 'name' => new \Quatrevieux\Form\View\FieldView("{$rootField}[name]", $value['name'] ?? null ?? 'example', ($__tmp_e32b6614c8f91a4bbb98de16ed0af4b5 = $errors['name'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_e32b6614c8f91a4bbb98de16ed0af4b5 : null, ['id' => 'form_name', 'type' => 'text'])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['count' => new \Quatrevieux\Form\View\FieldView('count', null, null, ['min' => 0, 'max' => 100, 'id' => 'form_count', 'type' => 'number']), 'name' => new \Quatrevieux\Form\View\FieldView('name', null ?? 'example', null, ['id' => 'form_name', 'type' => 'text'])], []) :  new \Quatrevieux\Form\View\FormView(['count' => new \Quatrevieux\Form\View\FieldView("{$rootField}[count]", null, null, ['min' => 0, 'max' => 100, 'id' => 'form_count', 'type' => 'number']), 'name' => new \Quatrevieux\Form\View\FieldView("{$rootField}[name]", null ?? 'example', null, ['id' => 'form_name', 'type' => 'text'])], []);
    }

    public function __construct(private Quatrevieux\Form\RegistryInterface $registry)
    {
    }
}

PHP,
            file_get_contents(self::GENERATED_DIR . '/Quatrevieux_Form_Fixtures_FormWithCustomViewViewGeneratorTesting.php')
        );

        $this->assertEquals(new FormView([
            'count' => new FieldView('count', null, null, ['type' => 'number', 'id' => 'form_count', 'min' => 0, 'max' => 100]),
            'name' => new FieldView('name', 'example', null, ['type' => 'text', 'id' => 'form_name']),
        ], []), $instantiator->default());
    }
}
