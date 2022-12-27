<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseResource;
use CourseHub\Course\Application\CourseResourceWriter;
use CourseHub\Course\Application\CourseToken;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DbalCourseResourceWriter implements CourseResourceWriter
{
    public function __construct(
        private Connection $connection
    ) {}

    public function create(CourseResource $courseResource): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('course_resource');
        $qb->values(
            [
                'uuid' => $qb->createNamedParameter($courseResource->getId()->value(), Types::GUID),
                'course_id' => $qb->createNamedParameter($courseResource->getCourseId()->value(), Types::GUID),
                'type' => $qb->createNamedParameter($courseResource->getType()->value(), Types::STRING),
                'title' => $qb->createNamedParameter($courseResource->getTitle()->value(), Types::STRING),
                'text' => $qb->createNamedParameter($courseResource->getText()->value(), Types::STRING),
                'url' => $qb->createNamedParameter($courseResource->getUrl()->value(), Types::STRING),
                'resource_id' => $qb->createNamedParameter($courseResource->getResourceId()->value(), Types::STRING),
                'dump' => $qb->createNamedParameter($courseResource->getDump()->value(), Types::STRING),
            ]
        );
        $qb->executeStatement();
    }

    public function delete(RequiredUuid $uuid): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('course_resource');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $qb->executeStatement();
    }

    public function update(CourseResource $courseResource): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('course_resource');
        $qb->where("uuid = {$qb->createNamedParameter($courseResource->getId()->value(), Types::GUID)}");
        $qb->set('title', $qb->createNamedParameter($courseResource->getTitle()->value()));
        $qb->set('text', $qb->createNamedParameter($courseResource->getText()->value()));
        $qb->set('resource_id', $qb->createNamedParameter($courseResource->getResourceId()->value()));
        $qb->executeStatement();
    }
}
