<?php declare(strict_types=1);

namespace CourseHub\Common\Domain;

use CourseHub\Common\Domain\Messages\Message;
use CourseHub\Common\Domain\Messages\MessagesArray;

abstract class Validator
{
    protected MessagesArray $errors;
    public function __construct()
    {
        $this->errors = MessagesArray::createEmpty();
    }

    protected function addMessage(Message $message): void
    {
        $this->errors->addMessage($message);
    }

    protected function getValidationErrors(): array
    {
        return $this->errors->toArrayOfMessages();
    }

    protected function hasValidationErrors(): bool
    {
        return (count($this->getValidationErrors()) > 0);
    }

    public function hasErrors(): bool
    {
        return $this->hasValidationErrors();
    }

    public function getErrors(): array
    {
        return $this->getValidationErrors();
    }
}