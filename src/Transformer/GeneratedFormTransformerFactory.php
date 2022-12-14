<?php

namespace Quatrevieux\Form\Transformer;

use Closure;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;

/**
 * Implementation of FormTransformerFactoryInterface using generated transformer instead of runtime one
 *
 * @extends AbstractGeneratedFactory<FormTransformerInterface>
 */
final class GeneratedFormTransformerFactory extends AbstractGeneratedFactory implements FormTransformerFactoryInterface
{
    /**
     * Fallback instantiator factory
     * Will be lazily initialized to {@see RuntimeFormTransformerFactory} if not passed in constructor
     *
     * @var FormTransformerFactoryInterface
     */
    private readonly FormTransformerFactoryInterface $factory;

    /**
     * Code generator
     * Will be lazily initialized if not passed in constructor
     *
     * @var FormTransformerGenerator
     */
    private readonly FormTransformerGenerator $generator;
    private readonly RegistryInterface $registry;

    /**
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameResolver Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     * @param FormTransformerFactoryInterface|null $factory Fallback instantiator factory.
     * @param FormTransformerGenerator|null $generator Code generator instance.
     */
    public function __construct(RegistryInterface $registry, ?FormTransformerFactoryInterface $factory = null, ?FormTransformerGenerator $generator = null, ?Closure $savePathResolver = null, ?Closure $classNameResolver = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('Transformer'),
            FormTransformerInterface::class
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
    public function create(string $dataClass): FormTransformerInterface
    {
        return $this->createOrGenerate($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function callConstructor(string $generatedClass): FormTransformerInterface
    {
        return new $generatedClass($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRuntime(string $dataClass): FormTransformerInterface
    {
        // @phpstan-ignore-next-line
        $factory = $this->factory ??= new RuntimeFormTransformerFactory($this->registry);
        return $factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        if (!$runtime instanceof RuntimeFormTransformer) {
            return null;
        }

        // @phpstan-ignore-next-line
        $generator = $this->generator ??= new FormTransformerGenerator($this->registry);
        return $generator->generate($generatedClassName, $runtime);
    }
}
