<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use Quatrevieux\Form\RegistryInterface;

/**
 * Apply transformers on each element of an array
 * If the input value is not and array, it will be transformed as an array before applying the transformers
 *
 * Example:
 * <code>
 * class MyForm
 * {
 *     #[TransformEach([
 *         new Trim(),
 *         new Csv(separator: ';'),
 *     ])]
 *     public ?array $tags = null;
 * }
 * </code>
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class TransformEach implements DelegatedFieldTransformerInterface
{
    public function __construct(
        /**
         * @var non-empty-list<FieldTransformerInterface|DelegatedFieldTransformerInterface>
         */
        public readonly array $transformers,

        /**
         * If true, transformation process will continue even if a sub-transformer fails,
         * and errors will be aggregated and reported on the field as array, for more precise error handling.
         *
         * @var bool
         */
        public readonly bool  $handleElementsErrors = false,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return new TransformEachImpl($registry);
    }
}
