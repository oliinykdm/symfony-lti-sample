<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseToken;
use CourseHub\Course\Application\CourseTokenReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;


final class DbalCourseTokenReader implements CourseTokenReader
{
    public function __construct(
        private Connection $connection
    ) {}

    private function generateCourseTokenFromData(
        $uuid,
        $courseId,
        $scope,
        $token,
        $timeCreated,
        $validTo,
        $lastAccess
    ): CourseToken
    {
        return new CourseToken(
            RequiredUuid::fromString($uuid),
            RequiredUuid::fromString($courseId),
            RequiredText::fromString($scope),
            RequiredUuid::fromString($token),
            RequiredText::fromString($timeCreated),
            RequiredText::fromString($validTo),
            RequiredText::fromString($lastAccess),
        );
    }

    public function findByCourseId(RequiredUuid $courseId): ?CourseToken
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'course_id', 'scope', 'token', 'time_created', 'valid_to', 'last_access');
        $qb->from('course_token');
        $qb->andWhere("course_id = {$qb->createNamedParameter($courseId->value(), Types::GUID)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->generateCourseTokenFromData(
            $row['uuid'],
            $row['course_id'],
            $row['scope'],
            $row['token'],
            $row['time_created'],
            $row['valid_to'],
            $row['last_access'],
        );
    }
}