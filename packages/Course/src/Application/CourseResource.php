<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class CourseResource
{
    public function __construct(
        public RequiredUuid $uuid,
        public RequiredUuid $courseId,
        public RequiredText $type,
        public RequiredText $title,
        public RequiredText $text,
        public RequiredText $url,
        public RequiredText $resourceId,
        public RequiredText $dump,
    ) {
    }

    public static function generate(
        RequiredUuid $uuid,
        RequiredUuid $courseId,
        RequiredText $type,
        RequiredText $title,
        RequiredText $text,
        RequiredText $url,
        RequiredText $resourceId,
        RequiredText $dump,
    ): CourseResource {
        return new self(
            $uuid,
            $courseId,
            $type,
            $title,
            $text,
            $url,
            $resourceId,
            $dump,
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
    public function getType(): RequiredText
    {
        return $this->type;
    }
    public function getTitle(): RequiredText
    {
        return $this->title;
    }
    public function getText(): RequiredText
    {
        return $this->text;
    }
    public function getUrl(): RequiredText
    {
        return $this->url;
    }
    public function getResourceId(): RequiredText
    {
        return $this->resourceId;
    }
    public function getDump(): RequiredText
    {
        return $this->dump;
    }
}