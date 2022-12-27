<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseResourceReader
{
    public function findByCourseId(RequiredUuid $courseId): ?array;
    public function findById(RequiredUuid $uuid): ?CourseResource;
    public function findAll(): ?array;
}