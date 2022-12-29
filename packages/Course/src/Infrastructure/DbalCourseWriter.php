<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Course;
use CourseHub\Course\Application\CourseResourceReader;
use CourseHub\Course\Application\CourseResourceWriter;
use CourseHub\Course\Application\CourseTokenWriter;
use CourseHub\Course\Application\CourseWriter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class DbalCourseWriter implements CourseWriter
{
    public function __construct(
        private Connection $connection,
        private CourseResourceWriter $courseResourceWriter,
        private CourseTokenWriter $courseTokenWriter,
    ) {}

    public function create(Course $course): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->insert('course');
        $qb->values(
            [
                'uuid' => $qb->createNamedParameter($course->getId()->value(), Types::GUID),
                'tool_name' => $qb->createNamedParameter($course->getToolName()->value(), Types::STRING),
                'tool_url' => $qb->createNamedParameter($course->getToolUrl()->value(), Types::STRING),
                'initiate_login_url' => $qb->createNamedParameter($course->getInitiateLoginUrl()->value(), Types::STRING),
                'jwks_url' => $qb->createNamedParameter($course->getJwksUrl()->value(), Types::STRING),
                'deep_linking_url' => $qb->createNamedParameter($course->getDeepLinkingUrl()->value(), Types::STRING),
                'client_id' => $qb->createNamedParameter($course->getClientId()->value(), Types::GUID),
                'deployment_id' => $qb->createNamedParameter($course->getDeploymentId()->value(), Types::GUID),
            ]
        );
        $qb->executeStatement();
    }

    public function delete(RequiredUuid $uuid): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->delete('course');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $qb->executeStatement();

        $this->courseResourceWriter->deleteByCourseId($uuid);
        $this->courseTokenWriter->deleteByCourseId($uuid);
    }

    public function update(Course $course): void
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->update('course');
        $qb->where("uuid = {$qb->createNamedParameter($course->getId()->value(), Types::GUID)}");
        $qb->set('tool_name', $qb->createNamedParameter($course->getToolName()->value()));
        $qb->set('initiate_login_url', $qb->createNamedParameter($course->getInitiateLoginUrl()->value()));
        $qb->set('tool_url', $qb->createNamedParameter($course->getToolUrl()->value()));
        $qb->set('jwks_url', $qb->createNamedParameter($course->getJwksUrl()->value()));
        $qb->set('deep_linking_url', $qb->createNamedParameter($course->getDeepLinkingUrl()->value()));
        if($course->getDump()) {
            $qb->set('dump', $qb->createNamedParameter($course->getDump()->value()));
        }
        $qb->executeStatement();
    }
}
