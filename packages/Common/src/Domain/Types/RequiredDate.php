<?php

declare(strict_types=1);

namespace CourseHub\Common\Domain\Types;

class RequiredDate
{
    private const FORMAT = 'Y-m-d H:i:s';
    private \DateTimeImmutable $value;

    public function __construct(string $value)
    {
        $this->value = \DateTimeImmutable::createFromFormat(self::FORMAT, $value);
    }

    public static function fromDateTimeImmutable(\DateTimeImmutable $value): static
    {
        return new static($value->format(self::FORMAT));
    }

    public function toString(): string
    {
        return $this->value->format(self::FORMAT);
    }

    public static function now(): static
    {
        $date = new \DateTimeImmutable();
        return new static($date->format(self::FORMAT));
    }
}