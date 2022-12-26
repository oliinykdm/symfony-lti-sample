<?php declare(strict_types=1);

namespace CourseHub\Common\Domain\Messages;

final class Message
{
    private string $messageContent;

    public function __construct(string $messageContent)
    {
        $this->messageContent = $messageContent;
    }

    public function toString(): string
    {
        return $this->messageContent;
    }

    public static function fromString(string $text): self
    {
        return new self($text);
    }
}