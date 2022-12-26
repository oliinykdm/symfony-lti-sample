<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseToken;
use CourseHub\Course\Application\CourseTokenWriter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DbalCourseTokenWriter implements CourseTokenWriter
{
    public function __construct(
        private Connection $connection
    ) {}

    public function create(CourseToken $courseToken): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('course_token');
        $qb->values(
            [
                'uuid' => $qb->createNamedParameter($courseToken->getId()->value(), Types::GUID),
                'course_id' => $qb->createNamedParameter($courseToken->getCourseId()->value(), Types::GUID),
                'scope' => $qb->createNamedParameter($courseToken->getScope()->value(), Types::STRING),
                'token' => $qb->createNamedParameter($courseToken->getToken()->value(), Types::GUID),
                'time_created' => $qb->createNamedParameter($courseToken->getTimeCreated()->value(), Types::STRING),
                'valid_to' => $qb->createNamedParameter($courseToken->getValidTo()->value(), Types::STRING),
                'last_access' => $qb->createNamedParameter($courseToken->getLastAccess()->value(), Types::STRING),
            ]
        );
        $qb->executeStatement();
    }

    public function delete(RequiredUuid $uuid): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('course_token');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $qb->executeStatement();
    }

    public function update(CourseToken $courseToken): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('course_token');
        $qb->where("uuid = {$qb->createNamedParameter($courseToken->getId()->value(), Types::GUID)}");
        $qb->set('course_id', $qb->createNamedParameter($courseToken->getCourseId()->value()));
        $qb->set('scope', $qb->createNamedParameter($courseToken->getScope()->value()));
        $qb->set('token', $qb->createNamedParameter($courseToken->getToken()->value()));
        $qb->set('time_created', $qb->createNamedParameter($courseToken->getTimeCreated()->value()));
        $qb->set('valid_to', $qb->createNamedParameter($courseToken->getValidTo()->value()));
        $qb->set('last_access', $qb->createNamedParameter($courseToken->getLastAccess()->value()));
        $qb->executeStatement();
    }
}
