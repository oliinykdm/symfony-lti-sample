<?php declare(strict_types=1);

namespace CourseHub\Course\Infrastructure;

use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\CourseResource;
use CourseHub\Course\Application\CourseResourceReader;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;


final class DbalCourseResourceReader implements CourseResourceReader
{
    public function __construct(
        private Connection $connection
    ) {}

    private function generateCourseResourceFromData(
        $uuid,
        $courseId,
        $type,
        $title,
        $text,
        $url,
        $resourceId,
        $dump,
    ): CourseResource
    {
        return new CourseResource(
            RequiredUuid::fromString($uuid),
            RequiredUuid::fromString($courseId),
            RequiredText::fromString($type),
            RequiredText::fromString($title),
            RequiredText::fromString($text),
            RequiredText::fromString($url),
            RequiredText::fromString($resourceId),
            RequiredText::fromString($dump),
        );
    }

    public function findByCourseId(RequiredUuid $courseId): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'course_id', 'type', 'title', 'text', 'url', 'resource_id', 'dump');
        $qb->from('course_resource');
        $qb->andWhere("course_id = {$qb->createNamedParameter($courseId->value(), Types::GUID)}");
        $rows = $qb->executeQuery()->fetchAllAssociative();

        if (!$rows) {
            return null;
        }

        $output = [];

        foreach($rows as $row) {
            $output[] = $this->generateCourseResourceFromData(
                $row['uuid'],
                $row['course_id'],
                $row['type'],
                $row['title'],
                $row['text'],
                $row['url'],
                $row['resource_id'],
                $row['dump'],
            );
        }

        return $output;
    }
    public function findById(RequiredUuid $uuid): ?CourseResource
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'course_id', 'type', 'title', 'text', 'url', 'resource_id', 'dump');
        $qb->from('course_resource');
        $qb->andWhere("uuid = {$qb->createNamedParameter($uuid->value(), Types::GUID)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

        return $this->generateCourseResourceFromData(
                $row['uuid'],
                $row['course_id'],
                $row['type'],
                $row['title'],
                $row['text'],
                $row['url'],
                $row['resource_id'],
                $row['dump'],
            );

    }

    public function findAll($orderBy = 'title', $sorting = 'desc'): ?array
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*');
        $qb->from('course_resource');
        $qb->orderBy($orderBy, $sorting);
        $rows = $qb->executeQuery()->fetchAllAssociative();
        if (!$rows) {
            return null;
        }
        return $rows;
    }
}