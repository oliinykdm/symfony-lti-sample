<?php

declare(strict_types=1);

namespace CourseHub\Common\Domain\Types;

class RequiredText
{
    public function __construct(protected string $value)
    {}

    public function value(): string
    {
        return $this->value;
    }
    public static function fromString($value): static
    {
        return new static($value);
    }
}