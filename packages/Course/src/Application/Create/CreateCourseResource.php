<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class CreateCourseResource
{
    public function __construct(
        public string $courseId,
        public string $type,
        public string $title,
        public string $text,
        public string $url,
        public string $resourceId,
        public string $dump,
    ) {}

    public function getCourseId(): RequiredUuid
    {
        return RequiredUuid::fromString($this->courseId);
    }
    public function getType(): RequiredText
    {
        return RequiredText::fromString($this->type);
    }
    public function getTitle(): RequiredText
    {
        return RequiredText::fromString($this->title);
    }
    public function getText(): RequiredText
    {
        return RequiredText::fromString($this->text);
    }
    public function getUrl(): RequiredText
    {
        return RequiredText::fromString($this->url);
    }
    public function getResourceId(): RequiredText
    {
        return RequiredText::fromString($this->resourceId);
    }
    public function getDump(): RequiredText
    {
        return RequiredText::fromString($this->dump);
    }

}
