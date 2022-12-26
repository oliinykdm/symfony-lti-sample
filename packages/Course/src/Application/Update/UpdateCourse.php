<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Update;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class UpdateCourse
{
    public function __construct(
        public string $uuid,
        public string $toolName,
        public string $toolUrl,
        public string $initiateLoginUrl,
        public string $jwksUrl,
        public string $deepLinkingUrl
    ) {}

    public function getUuid(): RequiredUuid
    {
        return RequiredUuid::fromString($this->uuid);
    }
    public function getToolName(): RequiredText
    {
        return RequiredText::fromString($this->toolName);
    }
    public function getToolUrl(): RequiredText
    {
        return RequiredText::fromString($this->toolUrl);
    }
    public function getInitiateLoginUrl(): RequiredText
    {
        return RequiredText::fromString($this->initiateLoginUrl);
    }
    public function getJwksUrl(): RequiredText
    {
        return RequiredText::fromString($this->jwksUrl);
    }
    public function getDeepLinkingUrl(): RequiredText
    {
        return RequiredText::fromString($this->deepLinkingUrl);
    }


}
