<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class Completion
{
    public function __construct(
        private RequiredUuid $uuid,
        private RequiredUuid $userId,
        private RequiredUuid $courseId,
        private RequiredText $score,
        private RequiredText $completionDate,
        private RequiredText $dump,
    ) {
    }

    public static function generate(
        RequiredUuid $uuid,
        RequiredUuid $userId,
        RequiredUuid $courseId,
        RequiredText $score,
        RequiredText $completionDate,
        RequiredText $dump,
    ): Completion {
        return new self(
            $uuid,
            $userId,
            $courseId,
            $score,
            $completionDate,
            $dump
        );
    }

    public function getId(): RequiredUuid
    {
        return $this->uuid;
    }

    public function getUserId(): RequiredUuid
    {
        return $this->userId;
    }

    public function getCourseId(): RequiredUuid
    {
        return $this->courseId;
    }

    public function getScore(): RequiredText
    {
        return $this->score;
    }

    public function getCompletionDate(): RequiredText
    {
        return $this->completionDate;
    }

    public function getDump(): RequiredText
    {
        return $this->dump;
    }

}