<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class CreateCompletion
{
    public function __construct(
        public string $userId,
        public string $courseId,
        public string $score,
        public string $completionDate,
        public string $dump,
    ) {}

    public function getUserId(): RequiredUuid
    {
        return RequiredUuid::fromString($this->userId);
    }
    public function getCourseId(): RequiredUuid
    {
        return RequiredUuid::fromString($this->courseId);
    }
    public function getScore(): RequiredText
    {
        return RequiredText::fromString($this->score);
    }
    public function getCompletionDate(): RequiredText
    {
        return RequiredText::fromString($this->completionDate);
    }
    public function getDump(): RequiredText
    {
        return RequiredText::fromString($this->dump);
    }


}
