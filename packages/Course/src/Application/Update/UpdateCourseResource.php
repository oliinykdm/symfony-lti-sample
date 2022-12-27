<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Update;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class UpdateCourseResource
{
    public function __construct(
        public string $uuid,
        public string $title,
        public string $text,
        public string $resourceId,
    ) {}

    public function getId(): RequiredUuid
    {
        return RequiredUuid::fromString($this->uuid);
    }
    public function getTitle(): RequiredText
    {
        return RequiredText::fromString($this->title);
    }
    public function getText(): RequiredText
    {
        return RequiredText::fromString($this->text);
    }
    public function getResourceId(): RequiredText
    {
        return RequiredText::fromString($this->resourceId);
    }
}
