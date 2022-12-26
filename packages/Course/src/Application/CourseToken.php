<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class CourseToken
{
    public function __construct(
        private RequiredUuid $uuid,
        private RequiredUuid $courseId,
        private RequiredText $scope,
        private RequiredUuid $token,
        private RequiredText $timeCreated,
        private RequiredText $validTo,
        private RequiredText $lastAccess,
    ) {
    }

    public static function generate(
        RequiredUuid $uuid,
        RequiredUuid $courseId,
        RequiredText $scope,
        RequiredUuid $token,
        RequiredText $timeCreated,
        RequiredText $validTo,
        RequiredText $lastAccess,
    ): CourseToken {
        return new self(
            $uuid,
            $courseId,
            $scope,
            $token,
            $timeCreated,
            $validTo,
            $lastAccess
        );
    }

    public function getId(): RequiredUuid
    {
        return $this->uuid;
    }

    public function getCourseId(): RequiredUuid
    {
        return $this->courseId;
    }

    public function getScope(): RequiredText
    {
        return $this->scope;
    }

    public function getToken(): RequiredUuid
    {
        return $this->token;
    }

    public function getTimeCreated(): RequiredText
    {
        return $this->timeCreated;
    }

    public function getValidTo(): RequiredText
    {
        return $this->validTo;
    }

    public function getLastAccess(): RequiredText
    {
        return $this->lastAccess;
    }
}