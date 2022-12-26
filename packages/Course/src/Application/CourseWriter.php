<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseWriter
{
    public function create(Course $course): void;
    public function update(Course $course): void;
    public function delete(RequiredUuid $uuid): void;
}