<?php

namespace Quatrevieux\Form;

use InvalidArgumentException;
use Quatrevieux\Form\Transformer\Field\ConfigurableFieldTransformerInterface;
use Quatrevieux\Form\Validator\Constraint\ConstraintValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function get_class;
use function sprintf;

/**
 * Base implementation of registry
 * This implementation simply use an array internally to store and retrieve instances
 */
final class DefaultRegistry implements RegistryInterface
{
    use RegistryTrait;

    /**
     * @var array<class-string<ConfigurableFieldTransformerInterface>, ConfigurableFieldTransformerInterface>
     */
    private array $transformers = [];

    /**
     * @var array<class-string<ConstraintValidatorInterface>, ConstraintValidatorInterface>
     */
    private array $validators = [];

    private ?TranslatorInterface $translator = null;

    /**
     * {@inheritdoc}
     */
    public function getTransformer(string $className): ConfigurableFieldTransformerInterface
    {
        // @phpstan-ignore-next-line
        return $this->transformers[$className] ?? throw new InvalidArgumentException(sprintf('Transformer "%s" is not registered', $className));
    }

    /**
     * {@inheritdoc}
     */
    public function getValidator(string $className): ConstraintValidatorInterface
    {
        // @phpstan-ignore-next-line
        return $this->validators[$className] ?? throw new InvalidArgumentException(sprintf('Validator "%s" is not registered', $className));
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslator(): TranslatorInterface
    {
        return $this->translator ?? DummyTranslator::instance();
    }

    /**
     * Register a transformer
     *
     * @param T $transformer Transformer instance
     * @param class-string<T>|null $className Class name of the transformer. If null, the class name of the instance will be used.
     *
     * @template T as ConfigurableFieldTransformerInterface
     */
    public function registerTransformer(ConfigurableFieldTransformerInterface $transformer, ?string $className = null): void
    {
        $this->transformers[$className ?? get_class($transformer)] = $transformer;
    }

    /**
     * Register a validator
     *
     * @param V $validator Validator instance
     * @param class-string<V>|null $className Class name of the validator. If null, the class name of the instance will be used.
     *
     * @template V as ConstraintValidatorInterface
     */
    public function registerValidator(ConstraintValidatorInterface $validator, ?string $className = null): void
    {
        $this->validators[$className ?? get_class($validator)] = $validator;
    }

    /**
     * Define the translator instance
     *
     * @param TranslatorInterface $translator
     * @return void
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }
}
