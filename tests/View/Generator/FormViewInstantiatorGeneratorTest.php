<?php

namespace Quatrevieux\Form\View\Generator;

use Quatrevieux\Form\Fixtures\SimpleRequest;
use Quatrevieux\Form\FormTestCase;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Validator\Constraint\Choice;
use Quatrevieux\Form\Validator\FieldError;
use Quatrevieux\Form\View\FieldView;
use Quatrevieux\Form\View\FormView;
use Quatrevieux\Form\View\Provider\FieldViewConfiguration;
use Quatrevieux\Form\View\Provider\FieldViewProviderConfigurationInterface;
use Quatrevieux\Form\View\Provider\FieldViewProviderInterface;
use Quatrevieux\Form\View\RuntimeFormViewInstantiator;

class FormViewInstantiatorGeneratorTest extends FormTestCase
{
    public function test_empty()
    {
        $generator = new FormViewInstantiatorGenerator($this->registry);

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([], $value) :  new \Quatrevieux\Form\View\FormView([], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([], []) :  new \Quatrevieux\Form\View\FormView([], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $generator->generate('GeneratedFormViewInstantiator', new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [], [], [], []))
        );
    }

    public function test_simple()
    {
        $generator = new FormViewInstantiatorGenerator($this->registry);
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
            'bar' => new FieldViewConfiguration(),
        ], [], [], []);

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, [])], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, [])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', null, null, []), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', null, null, [])], []) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", null, null, []), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", null, null, [])], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $generator->generate('GeneratedFormViewInstantiator', $instantiator)
        );
    }

    public function test_with_field_mapping()
    {
        $generator = new FormViewInstantiatorGenerator($this->registry);
        $instantiator = new RuntimeFormViewInstantiator($this->registry,
            SimpleRequest::class,
            [
                'foo' => new FieldViewConfiguration(),
                'bar' => new FieldViewConfiguration(),
            ],
            [
                'foo' => 'oof'
            ],
            [],
            [],
        );

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('oof', $value['oof'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, [])], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[oof]", $value['oof'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, [])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('oof', null, null, []), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', null, null, [])], []) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[oof]", null, null, []), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", null, null, [])], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $generator->generate('GeneratedFormViewInstantiator', $instantiator)
        );
    }

    public function test_with_attributes()
    {
        $generator = new FormViewInstantiatorGenerator($this->registry);
        $instantiator = new RuntimeFormViewInstantiator($this->registry,
            SimpleRequest::class,
            [
                'foo' => new FieldViewConfiguration(),
                'bar' => new FieldViewConfiguration(),
            ],
            [],
            [
                'foo' => ['class' => 'input'],
                'bar' => ['required' => true],
            ],
            [],
        );

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, ['class' => 'input']), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, ['required' => true])], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, ['class' => 'input']), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, ['required' => true])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', null, null, ['class' => 'input']), 'bar' => new \Quatrevieux\Form\View\FieldView('bar', null, null, ['required' => true])], []) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", null, null, ['class' => 'input']), 'bar' => new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", null, null, ['required' => true])], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $generator->generate('GeneratedFormViewInstantiator', $instantiator)
        );
    }

    public function test_use_fallback_generator()
    {
        $generator = new FormViewInstantiatorGenerator($this->registry);
        $instantiator = new RuntimeFormViewInstantiator($this->registry,
            SimpleRequest::class,
            [
                'foo' => new ViewProviderWithoutGenerator('azerty'),
            ],
            [],
            [],
            [],
        );

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => ($__view_885795100a2aeaa406bf3833fd45a7ea = new \Quatrevieux\Form\View\Generator\ViewProviderWithoutGenerator(foo: 'azerty'))->view($__view_885795100a2aeaa406bf3833fd45a7ea, 'foo', $value['foo'] ?? null, $errors['foo'] ?? null, [])], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => ($__view_885795100a2aeaa406bf3833fd45a7ea = new \Quatrevieux\Form\View\Generator\ViewProviderWithoutGenerator(foo: 'azerty'))->view($__view_885795100a2aeaa406bf3833fd45a7ea, "{$rootField}[foo]", $value['foo'] ?? null, $errors['foo'] ?? null, [])], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => ($__view_885795100a2aeaa406bf3833fd45a7ea = new \Quatrevieux\Form\View\Generator\ViewProviderWithoutGenerator(foo: 'azerty'))->view($__view_885795100a2aeaa406bf3833fd45a7ea, 'foo', null, null, [])], []) :  new \Quatrevieux\Form\View\FormView(['foo' => ($__view_885795100a2aeaa406bf3833fd45a7ea = new \Quatrevieux\Form\View\Generator\ViewProviderWithoutGenerator(foo: 'azerty'))->view($__view_885795100a2aeaa406bf3833fd45a7ea, "{$rootField}[foo]", null, null, [])], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $generator->generate('GeneratedFormViewInstantiator', $instantiator)
        );
    }

    public function test_with_choice()
    {
        $generator = new FormViewInstantiatorGenerator($this->registry);
        $instantiator = new RuntimeFormViewInstantiator($this->registry, SimpleRequest::class, [
            'foo' => new FieldViewConfiguration(),
            'bar' => new FieldViewConfiguration(),
        ], [], [], [
            'bar' => new Choice([12, 24, 48]),
        ]);

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    private $transformer;

    function submitted(array $value, array $errors, string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => (new \Quatrevieux\Form\View\FieldView('bar', $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, []))->choices((new \Quatrevieux\Form\Validator\Constraint\Choice(choices: [12, 24, 48], message: 'The value is not a valid choice.'))->choices($value['bar'] ?? null, $this->transformer->fieldTransformer('bar')), $this->registry->getTranslator())], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", $value['foo'] ?? null, ($__tmp_6f4afa00801630e2315251561e35e48c = $errors['foo'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_6f4afa00801630e2315251561e35e48c : null, []), 'bar' => (new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", $value['bar'] ?? null, ($__tmp_2b71b0f6b364f62d6344a8beead6aeb4 = $errors['bar'] ?? null) instanceof \Quatrevieux\Form\Validator\FieldError ? $__tmp_2b71b0f6b364f62d6344a8beead6aeb4 : null, []))->choices((new \Quatrevieux\Form\Validator\Constraint\Choice(choices: [12, 24, 48], message: 'The value is not a valid choice.'))->choices($value['bar'] ?? null, $this->transformer->fieldTransformer('bar')), $this->registry->getTranslator())], $value);
    }

    function default(string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView('foo', null, null, []), 'bar' => (new \Quatrevieux\Form\View\FieldView('bar', null, null, []))->choices((new \Quatrevieux\Form\Validator\Constraint\Choice(choices: [12, 24, 48], message: 'The value is not a valid choice.'))->choices(null, $this->transformer->fieldTransformer('bar')), $this->registry->getTranslator())], []) :  new \Quatrevieux\Form\View\FormView(['foo' => new \Quatrevieux\Form\View\FieldView("{$rootField}[foo]", null, null, []), 'bar' => (new \Quatrevieux\Form\View\FieldView("{$rootField}[bar]", null, null, []))->choices((new \Quatrevieux\Form\Validator\Constraint\Choice(choices: [12, 24, 48], message: 'The value is not a valid choice.'))->choices(null, $this->transformer->fieldTransformer('bar')), $this->registry->getTranslator())], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
        $this->transformer = $this->registry->getTransformerFactory()->create('Quatrevieux\\Form\\Fixtures\\SimpleRequest');
    }
}

PHP,
            $generator->generate('GeneratedFormViewInstantiator', $instantiator)
        );
    }
}

class ViewProviderWithoutGenerator implements FieldViewProviderConfigurationInterface, FieldViewProviderInterface
{
    public function __construct(
        private string $foo,
    ) {
    }

    public function getViewProvider(RegistryInterface $registry): FieldViewProviderInterface
    {
        return $this;
    }

    public function view(FieldViewProviderConfigurationInterface $configuration, string $name, mixed $value, array|FieldError|null $error, array $attributes): FieldView|FormView
    {
        return new FieldView($name, $value, $error, $attributes);
    }
}
