<?php

namespace Quatrevieux\Form\Transformer\Field;

use PHPUnit\Framework\TestCase;
use Quatrevieux\Form\RegistryInterface;

class FieldTransformersAggregateTest extends TestCase
{
    public function test_transform_with_FieldTransformerInterface_order()
    {
        $transformer = new FieldTransformersAggregate([
            new class implements FieldTransformerInterface {
                public function transformFromHttp(mixed $value): mixed
                {
                    return $value . '1';
                }

                public function transformToHttp(mixed $value): mixed
                {
                    return $value . '1';
                }

                public function canThrowError(): bool
                {
                    return false;
                }
            },
            new class implements FieldTransformerInterface {
                public function transformFromHttp(mixed $value): mixed
                {
                    return $value . '2';
                }

                public function transformToHttp(mixed $value): mixed
                {
                    return $value . '2';
                }

                public function canThrowError(): bool
                {
                    return false;
                }
            },
        ], $this->createStub(RegistryInterface::class));

        $this->assertEquals('foo12', $transformer->transformFromHttp('foo'));
        $this->assertEquals('foo21', $transformer->transformToHttp('foo'));
    }

    public function test_transform_with_DelegatedFieldTransformerInterface()
    {
        $transformer = new FieldTransformersAggregate([
            new class implements DelegatedFieldTransformerInterface {
                public function getTransformer(RegistryInterface $registry): ConfigurableFieldTransformerInterface
                {
                    return new class implements ConfigurableFieldTransformerInterface {
                        public function transformFromHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
                        {
                            return $value . 'a';
                        }

                        public function transformToHttp(DelegatedFieldTransformerInterface $configuration, mixed $value): mixed
                        {
                            return $value . 'b';
                        }

                        public function canThrowError(): bool
                        {
                            return false;
                        }
                    };
                }
            },
        ], $this->createStub(RegistryInterface::class));

        $this->assertEquals('fooa', $transformer->transformFromHttp('foo'));
        $this->assertEquals('foob', $transformer->transformToHttp('foo'));
    }

    public function test_canThrowError()
    {
        $transformer = new FieldTransformersAggregate([
            new class implements FieldTransformerInterface {
                public function transformFromHttp(mixed $value): mixed
                {
                    return $value;
                }

                public function transformToHttp(mixed $value): mixed
                {
                    return $value;
                }

                public function canThrowError(): bool
                {
                    return false;
                }
            },
        ], $this->createStub(RegistryInterface::class));

        $this->assertTrue($transformer->canThrowError());
    }
}
