<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Messages\Message;
use CourseHub\Common\Domain\Validator;
use CourseHub\Course\Application\Create\CreateCourse;
use CourseHub\Course\Application\Update\UpdateCourse;

final class CourseValidator extends Validator
{
    public function validateCreate(CreateCourse $command): void
    {
        $this->validateToolNameNotEmpty($command->getToolName()->value());
        $this->validateToolUrlNotEmpty($command->getToolUrl()->value());
        $this->validateInitiateLoginUrlNotEmpty($command->getInitiateLoginUrl()->value());
        $this->validateJwksUrlNotEmpty($command->getJwksUrl()->value());

    }

    public function validateUpdate(UpdateCourse $command): void
    {
        $this->validateToolNameNotEmpty($command->getToolName()->value());
        $this->validateToolUrlNotEmpty($command->getToolUrl()->value());
        $this->validateInitiateLoginUrlNotEmpty($command->getInitiateLoginUrl()->value());
        $this->validateJwksUrlNotEmpty($command->getJwksUrl()->value());

    }

    public function validateToolNameNotEmpty($value): bool
    {
        if(strlen($value) < 1 || strlen($value) > 200) {
            $this->addMessage(Message::fromString('Tool Name must be between 1 and 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }

    public function validateToolUrlNotEmpty($value): bool
    {
        if(strlen($value) < 1 || strlen($value) > 200) {
            $this->addMessage(Message::fromString('Tool Url must be between 1 and 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }

    public function validateInitiateLoginUrlNotEmpty($value): bool
    {
        if(strlen($value) < 1 || strlen($value) > 200) {
            $this->addMessage(Message::fromString('Initiate Login Url must be between 1 and 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }

    public function validateJwksUrlNotEmpty($value): bool
    {
        if(strlen($value) < 1 || strlen($value) > 200) {
            $this->addMessage(Message::fromString('JWKS Url must be between 1 and 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }
}