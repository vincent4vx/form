<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;

/**
 * Apply transformers on each element of an array
 * If the input value is not and array, it will be transformed as an array before applying the transformers
 *
 * Note: sub-transformers errors are not handled by this transformer, so the error will be reported on the field
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
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
final class TransformEach implements DelegatedFieldTransformerInterface
{
    public function __construct(
        /**
         * @var non-empty-list<FieldTransformerInterface|DelegatedFieldTransformerInterface>
         */
        public readonly array $transformers
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformer(FieldTransformerRegistryInterface $registry): ConfigurableFieldTransformerInterface
    {
        return new TransformEachImpl($registry);
    }
}
