<?php

namespace Quatrevieux\Form\Validator;

use Closure;
use Quatrevieux\Form\Util\AbstractGeneratedFactory;
use Quatrevieux\Form\Util\Functions;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorRegistryInterface;
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
     *
     * @var ValidatorFactoryInterface
     */
    private readonly ValidatorFactoryInterface $factory;

    /**
     * Code generator
     *
     * @var ValidatorGenerator
     */
    private readonly ValidatorGenerator $generator;
    private readonly ConstraintValidatorRegistryInterface $validatorRegistry;

    /**
     * @param ValidatorFactoryInterface $factory Fallback instantiator factory.
     * @param ValidatorGenerator $generator Code generator instance.
     * @param (Closure(string):string)|null $savePathResolver Resolve instatiator class file path using instantiator class name as parameter. By default, save into `sys_get_temp_dir()`
     * @param (Closure(string):string)|null $classNameResolver Resolve instantiator class name using DTO class name as parameter. By default, replace namespace seprator by "_", and add "Instantiator" suffix
     */
    public function __construct(ValidatorFactoryInterface $factory, ValidatorGenerator $generator, ConstraintValidatorRegistryInterface $validatorRegistry, ?Closure $savePathResolver = null, ?Closure $classNameResolver = null)
    {
        parent::__construct(
            $savePathResolver ?? Functions::savePathResolver(),
            $classNameResolver ?? Functions::classNameResolver('Validator'),
            ValidatorInterface::class
        );

        $this->factory = $factory;
        $this->generator = $generator;
        $this->validatorRegistry = $validatorRegistry;
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
        return new $generatedClass($this->validatorRegistry);
    }

    /**
     * {@inheritdoc}
     */
    protected function createRuntime(string $dataClass): ValidatorInterface
    {
        return $this->factory->create($dataClass);
    }

    /**
     * {@inheritdoc}
     */
    protected function generate(string $generatedClassName, object $runtime): ?string
    {
        return $this->generator->generate($generatedClassName, $runtime);
    }
}
