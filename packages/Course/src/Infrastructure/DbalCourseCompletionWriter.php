<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Completion;
use CourseHub\Course\Application\CourseCompletionWriter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DbalCourseCompletionWriter implements CourseCompletionWriter
{
    public function __construct(
        private Connection $connection
    ) {}

    public function create(Completion $completion): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('course_completion');
        $qb->values(
            [
                'uuid' => $qb->createNamedParameter($completion->getId()->value(), Types::GUID),
                'user_id' => $qb->createNamedParameter($completion->getUserId()->value(), Types::GUID),
                'course_id' => $qb->createNamedParameter($completion->getCourseId()->value(), Types::GUID),
                'score' => $qb->createNamedParameter($completion->getScore()->value(), Types::STRING),
                'completion_date' => $qb->createNamedParameter(new \DateTimeImmutable($completion->getCompletionDate()->value()), Types::DATETIME_IMMUTABLE),
                'dump' => $qb->createNamedParameter($completion->getDump()->value(), Types::STRING),
            ]
        );
        $qb->executeStatement();
    }

    public function delete(RequiredUuid $uuid): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('course_completion');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $qb->executeStatement();
    }

    public function update(Completion $completion): void
    {

    }
}
