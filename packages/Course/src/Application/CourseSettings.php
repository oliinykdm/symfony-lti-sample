<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class CourseSettings
{
    public function __construct(
        private RequiredText $keyType,
        private RequiredText $publicKeySet,
        private RequiredText $initiateLogin,
        private RequiredText $redirectionOnUris,
        private RequiredText $customParameters,
        private RequiredText $courseVisible,
        private RequiredText $launchContainer,
        private RequiredText $contentItem,
        private RequiredText $ltiServiceGradeSynchronization,
        private RequiredText $ltiServiceMemberships,
        private RequiredText $ltiServiceToolSettings,
        private RequiredText $sendName,
        private RequiredText $sendeMailAddr,
        private RequiredText $acceptGrades,
        private RequiredText $organizationIdDefault,
        private RequiredText $organizationId,
        private RequiredText $organizationUrl,
        private RequiredText $forceSsl,
        private RequiredText $serviceSalt
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
    ): CourseSettings {
        return new self(
            $uuid,
            $toolName,
            $toolUrl,
            $initiateLoginUrl,
            $jwksUrl,
            $deepLinkingUrl,
            is_null($clientId) ? null : $clientId,
            is_null($deploymentId) ? null : $deploymentId
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
}