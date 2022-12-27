<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Messages\Message;
use CourseHub\Common\Domain\Validator;
use CourseHub\Course\Application\Update\UpdateCourseResource;

final class CourseResourceValidator extends Validator
{
    public function validateUpdate(UpdateCourseResource $command): void
    {
        $this->validateTitleNotEmpty($command->getTitle()->value());
        $this->validateTextNotBig($command->getText()->value());
        $this->validateResourceIdNotEmpty($command->getResourceId()->value());
    }

    public function validateTitleNotEmpty($text): bool
    {
        if(strlen($text) < 1 || strlen($text) > 200) {
            $this->addMessage(Message::fromString('Resource Title must be between 1 and 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }

    public function validateTextNotBig($text): bool
    {
        if(strlen($text) > 200) {
            $this->addMessage(Message::fromString('Resource Text must less than 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }

    public function validateResourceIdNotEmpty($text): bool
    {
        if(strlen($text) < 1 || strlen($text) > 200) {
            $this->addMessage(Message::fromString('Resource Resource Id must be between 1 and 200 characters'));
            return false;
        }
        else {
            return true;
        }
    }

}