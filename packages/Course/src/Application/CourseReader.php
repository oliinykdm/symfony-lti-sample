<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Course;

interface CourseReader
{
    public function findByClientId(RequiredUuid $clientId): ?Course;
    public function findById(RequiredUuid $uuid): ?Course;
    public function findAll(): ?array;
}