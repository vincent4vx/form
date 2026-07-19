<?php

namespace Quatrevieux\Form\DataMapper;

use Quatrevieux\Form\Util\Code;
use Quatrevieux\Form\Util\Expr;
use ReflectionClass;
use stdClass;

use function is_object;

final class ConstructorParameterMetadata
{
    public function __construct(
        public readonly string $name,
        public readonly mixed $fallback,
        public readonly bool $required,
        public readonly string $requiredMessage,
    ) {}

    public function compiledFallback(): string
    {
        if (is_object($this->fallback) && $this->fallback::class !== stdClass::class) {
            return (string) Expr::new(ReflectionClass::class, [$this->fallback::class])->newInstanceWithoutConstructor();
        }

        return Code::value($this->fallback);
    }
}
