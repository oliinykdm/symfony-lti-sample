<?php

declare(strict_types=1);

namespace CourseHub\Common\Domain\Types;

class RequiredNumber
{
    public function __construct(protected int $value)
    {}

    public function value(): int
    {
        return $this->value;
    }
    public static function fromString($value): static
    {
        return new static($value);
    }
}