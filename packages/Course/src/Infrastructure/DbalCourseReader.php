<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Course;
use CourseHub\Course\Application\CourseReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DbalCourseReader implements CourseReader
{
    public function __construct(
        private Connection $connection
    ) {}

    public function findAll($orderBy = 'tool_name', $sorting = 'desc'): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from('course');
        $qb->orderBy($orderBy, $sorting);
        $rows = $qb->executeQuery()->fetchAllAssociative();
        if (!$rows) {
            return null;
        }
        return $rows;
    }

    public function findByClientId(RequiredUuid $clientId): ?Course
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'tool_name', 'tool_url', 'initiate_login_url', 'jwks_url', 'deep_linking_url', 'client_id', 'deployment_id', 'dump');
        $qb->from('course');
        $qb->andWhere("client_id = {$qb->createNamedParameter($clientId->value(), Types::GUID)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->generateCourseFromData(
            $row['uuid'],
            $row['tool_name'],
            $row['tool_url'],
            $row['initiate_login_url'],
            $row['jwks_url'],
            $row['deep_linking_url'],
            $row['client_id'],
            $row['deployment_id'],
            $row['dump'],
        );
    }

    public function findById(RequiredUuid $uuid): ?Course
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'tool_name', 'tool_url', 'initiate_login_url', 'jwks_url', 'deep_linking_url', 'client_id', 'deployment_id', 'dump');
        $qb->from('course');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->generateCourseFromData(
            $row['uuid'],
            $row['tool_name'],
            $row['tool_url'],
            $row['initiate_login_url'],
            $row['jwks_url'],
            $row['deep_linking_url'],
            $row['client_id'],
            $row['deployment_id'],
            $row['dump'],
        );
    }

    private function generateCourseFromData(
        $uuid,
        $toolName,
        $toolUrl,
        $initiateLoginUrl,
        $jwksUrl,
        $deepLinkingUrl,
        $clientId,
        $deploymentId,
        $dump,
    ): Course
    {
        return new Course(
            RequiredUuid::fromString($uuid),
            RequiredText::fromString($toolName),
            RequiredText::fromString($toolUrl),
            RequiredText::fromString($initiateLoginUrl),
            RequiredText::fromString($jwksUrl),
            RequiredText::fromString($deepLinkingUrl),
            RequiredUuid::fromString($clientId),
            RequiredUuid::fromString($deploymentId),
            $dump ? RequiredText::fromString($dump) : null,
        );
    }
}