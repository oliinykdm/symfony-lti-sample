<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class CreateCourseToken
{
    public function __construct(
        private string $uuid,
        private string $courseId,
        private string $scope,
        private string $token,
        private string $timeCreated,
        private string $validTo,
        private string $lastAccess,
    ) {}

    public function getId(): RequiredUuid
    {
        return RequiredUuid::fromString($this->uuid);
    }

    public function getCourseId(): RequiredUuid
    {
        return RequiredUuid::fromString($this->courseId);
    }

    public function getScope(): RequiredText
    {
        return RequiredText::fromString($this->scope);
    }

    public function getToken(): RequiredUuid
    {
        return RequiredUuid::fromString($this->token);
    }

    public function getTimeCreated(): RequiredText
    {
        return RequiredText::fromString($this->timeCreated);
    }

    public function getValidTo(): RequiredText
    {
        return RequiredText::fromString($this->validTo);
    }

    public function getLastAccess(): RequiredText
    {
        return RequiredText::fromString($this->lastAccess);
    }

}
