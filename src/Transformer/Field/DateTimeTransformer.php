<?php

namespace Quatrevieux\Form\Transformer\Field;

use Attribute;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use InvalidArgumentException;

use Quatrevieux\Form\Transformer\Generator\FieldTransformerGeneratorInterface;

use Quatrevieux\Form\Transformer\Generator\FormTransformerGenerator;

use Quatrevieux\Form\Util\Call;
use Quatrevieux\Form\Util\Code;

use Quatrevieux\Form\Util\Expr;

use function is_scalar;

/**
 * Transform a date from a string to a DateTimeInterface object.
 *
 * Usage:
 * <code>
 * class MyForm
 * {
 *     // Parse an HTML5 datetime-local input
 *     #[DateTimeTransformer]
 *     public ?DateTimeInterface $date;
 *
 *     // Use a custom format, class, and timezone
 *     #[DateTimeTransformer(class: DateTime::class, format: 'd/m/Y', timezone: 'Europe/Paris')]
 *     public ?DateTime $date;
 * }
 * </code>
 *
 * @implements FieldTransformerGeneratorInterface<DateTimeTransformer>
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class DateTimeTransformer implements FieldTransformerInterface, FieldTransformerGeneratorInterface
{
    public function __construct(
        /**
         * The format of the date, used for parsing and formatting.
         * The format must be compatible with {@see DateTime::createFromFormat()}.
         *
         * By default, use the HTML5 datetime-local format without seconds.
         */
        private readonly string $format = 'Y-m-d\TH:i',

        /**
         * The timezone of the date.
         *
         * Use timezone identifiers used by {@see DateTimeZone::__construct()}.
         * If null, the timezone of the server will be used.
         */
        private readonly ?string $timezone = null,

        /**
         * The class of the date created by the transformer.
         * This class must implement DateTimeInterface and method `createFromFormat`.
         *
         * @var class-string<DateTimeInterface>
         */
        private readonly string $class = DateTimeImmutable::class,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function transformFromHttp(mixed $value): ?DateTimeInterface
    {
        if ($value instanceof $this->class) {
            return $value;
        }

        if (!is_scalar($value)) {
            return null;
        }

        /** @var class-string<DateTimeImmutable> $class Allows phpstan to resolve method `createFromFormat` */
        $class = $this->class;
        $timezone = $this->timezone ? new DateTimeZone($this->timezone) : null;

        if ($date = $class::createFromFormat('!' . $this->format, (string) $value, $timezone)) {
            return $date;
        }

        throw new InvalidArgumentException('The given value is not a valid date.');
    }

    /**
     * {@inheritdoc}
     */
    public function transformToHttp(mixed $value): ?string
    {
        if (!$value instanceof DateTimeInterface) {
            return null;
        }

        if ($this->timezone) {
            if (!$value instanceof DateTimeImmutable) {
                $value = clone $value;
            }

            $value = $value->setTimezone(new DateTimeZone($this->timezone));
        }

        return $value->format($this->format);
    }

    /**
     * {@inheritdoc}
     */
    public function canThrowError(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformFromHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $tmpVar = Expr::varName($previousExpression, 'date');
        $className = '\\' . $transformer->class;
        $createDate = Call::static($className)->createFromFormat(
            '!' . $transformer->format,
            $tmpVar,
            $transformer->timezone ? new DateTimeZone($transformer->timezone) : null
        );
        $createDate .= ' ?: throw ' . Code::new(InvalidArgumentException::class, ['The given value is not a valid date.']);

        return "(({$tmpVar} = {$previousExpression}) instanceof {$className} ? {$tmpVar} : (is_scalar({$tmpVar}) ? ({$createDate}) : null))";
    }

    /**
     * {@inheritdoc}
     */
    public function generateTransformToHttp(object $transformer, string $previousExpression, FormTransformerGenerator $generator): string
    {
        $tmpVar = Expr::varName($previousExpression, 'date');
        $format = Code::value($transformer->format);
        $dateExpression = $transformer->timezone
            ? $tmpVar->format("({} instanceof \DateTimeImmutable ? {} : clone {})")->setTimezone(new DateTimeZone($transformer->timezone))
            : $tmpVar
        ;

        return "(({$tmpVar} = {$previousExpression}) instanceof \DateTimeInterface ? {$dateExpression}->format({$format}) : null)";
    }
}
