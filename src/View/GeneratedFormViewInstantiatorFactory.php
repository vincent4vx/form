<?php

namespace Quatrevieux\Form\View;

use Closure;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\View\Generator\FormViewInstantiatorGenerator;

/**
 * Implementation of FormViewInstantiatorFactoryInterface using generated view instantiator instead of runtime one
 *
 * @extends AbstractGeneratedFactory<FormViewInstantiatorInterface>
 */
final class GeneratedFormViewInstantiatorFactory extends AbstractGeneratedFactory implements FormViewInstantiatorFactoryInterface
{
    /**
     * Fallback view instantiator factory
     * Will be lazily initialized to {@see RuntimeFormViewInstantiatorFactory} if not passed in constructor
     *
     * @var FormViewInstantiatorFactoryInterface|null
     */
    private ?FormViewInstantiatorFactoryInterface $factory = null;

    /**
     * Code generator
     * Will be lazily initialized if not passed in constructor
     *
     * @var FormViewInstantiatorGenerator|null
     */
    private ?FormViewInstantiatorGenerator $generator = null;
    private readonly RegistryInterface $registry;

    /**
     * @param (Closure(string):string)|null $savePathResolver Resolve view instantiator class file path using view class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameResolver Resolve view instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "ViewInstantiator" suffix
     * @param FormViewInstantiatorFactoryInterface|null $factory Fallback view instantiator factory.
     * @param FormViewInstantiatorGenerator|null $generator Code generator instance.
     */
    public function __construct(RegistryInterface $registry, ?FormViewInstantiatorFactoryInterface $factory = null, ?FormViewInstantiatorGenerator $generator = null, ?Closure $savePathResolver = null, ?Closure $classNameResolver = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('ViewInstantiator'),
            FormViewInstantiatorInterface::class
        );

        $this->registry = $registry;

        if ($factory) {
            $this->factory = $factory;
        }

        if ($generator) {
            $this->generator = $generator;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $dataClassName): FormViewInstantiatorInterface
    {
        return $this->createOrGenerate($dataClassName);
    }

    /**
     * {@inheritdoc}
     */
    protected function callConstructor(string $generatedClass): object
    {
        return new $generatedClass($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRuntime(string $dataClass): object
    {
        $factory = $this->factory ??= new RuntimeFormViewInstantiatorFactory($this->registry);

        return $factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        if (!$runtime instanceof RuntimeFormViewInstantiator) {
            return null;
        }

        $generator = $this->generator ??= new FormViewInstantiatorGenerator($this->registry);

        return $generator->generate($generatedClassName, $runtime);
    }
}
