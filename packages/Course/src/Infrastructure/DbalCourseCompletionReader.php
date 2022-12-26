<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Completion;
use CourseHub\Course\Application\CourseCompletionReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;


final class DbalCourseCompletionReader implements CourseCompletionReader
{
    public function __construct(
        private Connection $connection
    ) {}

    private function generateCourseCompletionFromData(
        $uuid,
        $userId,
        $courseId,
        $score,
        $completionDate,
        $dump
    ): Completion
    {
        return new Completion(
            RequiredUuid::fromString($uuid),
            RequiredUuid::fromString($userId),
            RequiredUuid::fromString($courseId),
            RequiredText::fromString($score),
            RequiredText::fromString($completionDate),
            RequiredText::fromString($dump),
        );
    }

    public function findByCourseAndUserId(RequiredUuid $courseId, RequiredUuid $userId): ?Completion
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'user_id', 'course_id', 'score', 'completion_date', 'dump');
        $qb->from('course_completion');
        $qb->andWhere("user_id = {$qb->createNamedParameter($userId->value(), Types::GUID)}");
        $qb->andWhere("course_id = {$qb->createNamedParameter($courseId->value(), Types::GUID)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->generateCourseCompletionFromData(
            $row['uuid'],
            $row['user_id'],
            $row['course_id'],
            $row['score'],
            $row['completion_date'],
            $row['dump'],
        );
    }

    public function findById(RequiredUuid $uuid): ?Completion
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'user_id', 'course_id', 'score', 'completion_date', 'dump');
        $qb->from('course_completion');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->generateCourseCompletionFromData(
            $row['uuid'],
            $row['user_id'],
            $row['course_id'],
            $row['score'],
            $row['completion_date'],
            $row['dump'],
        );
    }
}