<?php declare(strict_types=1);

namespace CourseHub\Common\Domain\Messages;

final class MessagesArray
{
    private array $messages;

    public function __construct(array $messages)
    {
        $this->messages = $messages;
    }

    public static function createEmpty(): self
    {
        return new self([]);
    }

    public function addMessage(Message $message): void
    {
        $this->messages[] = $message->toString();
    }

    public function toArrayOfMessages(): array
    {
        return $this->messages;
    }
}