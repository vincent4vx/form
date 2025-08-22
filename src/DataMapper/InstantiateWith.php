<?php

namespace Quatrevieux\Form\DataMapper;

use Attribute;

/**
 * Define data mapper class to use for instantiate data object
 *
 * Usage:
 * <code>
 * #[InstantiateWith(CustomDataMapper::class)]
 * class FormWithCustomDataMapper
 * {
 *    //...
 * }
 * </code>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class InstantiateWith implements DataMapperProviderInterface
{
    public function __construct(
        /**
         * @var class-string<DataMapperInterface>
         */
        public readonly string $dataMapperClassName,
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getDataMapper(string $dataClassName): DataMapperInterface
    {
        $className = $this->dataMapperClassName;
        return new $className($dataClassName);
    }
}
