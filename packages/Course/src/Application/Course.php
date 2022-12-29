<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class Course
{
    public function __construct(
        private RequiredUuid $uuid,
        private RequiredText $toolName,
        private RequiredText $toolUrl,
        private RequiredText $initiateLoginUrl,
        private RequiredText $jwksUrl,
        private RequiredText $deepLinkingUrl,
        private ?RequiredUuid $clientId,
        private ?RequiredUuid $deploymentId,
        private ?RequiredText $dump,
    ) {
    }

    public static function generate(
        RequiredUuid $uuid,
        RequiredText $toolName,
        RequiredText $toolUrl,
        RequiredText $initiateLoginUrl,
        RequiredText $jwksUrl,
        RequiredText $deepLinkingUrl,
        ?RequiredUuid $clientId,
        ?RequiredUuid $deploymentId,
        ?RequiredText $dump,
    ): Course {
        return new self(
            $uuid,
            $toolName,
            $toolUrl,
            $initiateLoginUrl,
            $jwksUrl,
            $deepLinkingUrl,
            is_null($clientId) ? null : $clientId,
            is_null($deploymentId) ? null : $deploymentId,
            is_null($dump) ? null : $dump,
        );
    }

    public function getId(): RequiredUuid
    {
        return $this->uuid;
    }
    public function getToolName(): RequiredText
    {
        return $this->toolName;
    }
    public function getToolUrl(): RequiredText
    {
        return $this->toolUrl;
    }
    public function getInitiateLoginUrl(): RequiredText
    {
        return $this->initiateLoginUrl;
    }
    public function getJwksUrl(): RequiredText
    {
        return $this->jwksUrl;
    }
    public function getDeepLinkingUrl(): RequiredText
    {
        return $this->deepLinkingUrl;
    }
    public function getClientId(): RequiredUuid
    {
        return $this->clientId;
    }
    public function getDeploymentId(): RequiredUuid
    {
        return $this->deploymentId;
    }
    public function getDump(): ?RequiredText
    {
        return $this->dump ?? null;
    }
}