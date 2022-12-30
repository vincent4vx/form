<?php

namespace Quatrevieux\Form\Validator;

use Closure;
use Quatrevieux\Form\RegistryInterface;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\Generator\ValidatorGenerator;

/**
 * Implentation of InstantiatorFactoryInterface using generated instantiator instead of runtime one
 *
 * @extends AbstractGeneratedFactory<ValidatorInterface>
 */
final class GeneratedValidatorFactory extends AbstractGeneratedFactory implements ValidatorFactoryInterface
{
    /**
     * Fallback instantiator factory
     * Will be lazily instantiated to {@see RuntimeValidatorFactory} if not provided in constructor
     *
     * @var ValidatorFactoryInterface
     */
    private readonly ValidatorFactoryInterface $factory;

    /**
     * Code generator
     * Will be lazily instantiated if not provided in constructor
     *
     * @var ValidatorGenerator
     */
    private readonly ValidatorGenerator $generator;
    private readonly RegistryInterface $registry;

    /**
     * @param RegistryInterface $registry Registry instance.
     * @param ValidatorFactoryInterface|null $factory Fallback instantiator factory. If not provided, will be lazily instantiated to {@see RuntimeValidatorFactory}
     * @param ValidatorGenerator|null $generator Code generator instance. If not provided, will be lazily instantiated.
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(class-string):string)|null $classNameResolver Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     */
    public function __construct(RegistryInterface $registry, ?ValidatorFactoryInterface $factory = null, ?ValidatorGenerator $generator = null, ?Closure $savePathResolver = null, ?Closure $classNameResolver = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('Validator'),
            ValidatorInterface::class
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
    public function create(string $dataClass): ValidatorInterface
    {
        return $this->createOrGenerate($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function callConstructor(string $generatedClass): ValidatorInterface
    {
        return new $generatedClass($this->registry);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRuntime(string $dataClass): ValidatorInterface
    {
        // @phpstan-ignore-next-line
        $factory = $this->factory ??= new RuntimeValidatorFactory($this->registry);
        return $factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        // @todo handle other validator instances ?
        if ($runtime instanceof RuntimeValidator) {
            // @phpstan-ignore-next-line
            $generator = $this->generator ??= new ValidatorGenerator($this->registry);
            return $generator->generate($generatedClassName, $runtime);
        }

        return null;
    }
}
