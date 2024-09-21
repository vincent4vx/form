<?php

namespace Quatrevieux\Form\View\Generator;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\Util\Code;

class FormViewInstantiatorClassTest extends TestCase
{
    public function test_empty()
    {
        $class = new FormViewInstantiatorClass('GeneratedFormViewInstantiator');

        $class->generateDefault();
        $class->generateSubmitted();

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, ?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([], $value) :  new \Quatrevieux\Form\View\FormView([], $value);
    }

    function default(?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([], []) :  new \Quatrevieux\Form\View\FormView([], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $class->code()
        );
    }

    public function test_with_property()
    {
        $class = new FormViewInstantiatorClass('GeneratedFormViewInstantiator');
        $class->property('foo', 'new Foo()');

        $class->generateDefault();
        $class->generateSubmitted();

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    private $foo;

    function submitted(array $value, array $errors, ?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([], $value) :  new \Quatrevieux\Form\View\FormView([], $value);
    }

    function default(?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([], []) :  new \Quatrevieux\Form\View\FormView([], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
        $this->foo = new Foo();
    }
}

PHP,
            $class->code()
        );
    }

    public function test_single_field()
    {
        $class = new FormViewInstantiatorClass('GeneratedFormViewInstantiator');

        $class->declareFieldView('foo', '_foo', function ($field, $httpField, $rootField) { $rootField ??= 'null'; return "create_field($field, $httpField, $rootField)"; });

        $class->generateDefault();
        $class->generateSubmitted();

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, ?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => create_field($value['_foo'] ?? null, $errors['foo'] ?? null, null)], $value) :  new \Quatrevieux\Form\View\FormView(['foo' => create_field($value['_foo'] ?? null, $errors['foo'] ?? null, $rootField)], $value);
    }

    function default(?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView(['foo' => create_field(null, null, null)], []) :  new \Quatrevieux\Form\View\FormView(['foo' => create_field(null, null, $rootField)], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $class->code()
        );
    }

    public function test_with_list()
    {
        $class = new FormViewInstantiatorClass('GeneratedFormViewInstantiator');

        $class->declareFieldView(0, 0, function ($field, $httpField, $rootField) { return "create_field($field, $httpField, \"{{$rootField}}[0]\")"; });
        $class->declareFieldView(1, 1, function ($field, $httpField, $rootField) { return "create_field($field, $httpField, \"{{$rootField}}[1]\")"; });

        $class->generateDefault();
        $class->generateSubmitted();

        $this->assertSame(
            <<<'PHP'
<?php

class GeneratedFormViewInstantiator implements Quatrevieux\Form\View\FormViewInstantiatorInterface
{
    function submitted(array $value, array $errors, ?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([create_field($value['0'] ?? null, $errors[0] ?? null, "{}[0]"), create_field($value['1'] ?? null, $errors[1] ?? null, "{}[1]")], $value) :  new \Quatrevieux\Form\View\FormView([create_field($value['0'] ?? null, $errors[0] ?? null, "{$rootField}[0]"), create_field($value['1'] ?? null, $errors[1] ?? null, "{$rootField}[1]")], $value);
    }

    function default(?string $rootField = null): Quatrevieux\Form\View\FormView
    {
        return $rootField === null ? new \Quatrevieux\Form\View\FormView([create_field(null, null, "{}[0]"), create_field(null, null, "{}[1]")], []) :  new \Quatrevieux\Form\View\FormView([create_field(null, null, "{$rootField}[0]"), create_field(null, null, "{$rootField}[1]")], []);
    }

    public function __construct(
        private Quatrevieux\Form\RegistryInterface $registry,
    ) {
    }
}

PHP,
            $class->code()
        );
    }
}
