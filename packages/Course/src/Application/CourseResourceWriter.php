<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseResourceWriter
{
    public function create(CourseResource $courseResource): void;
    public function update(CourseResource $courseResource): void;
    public function delete(RequiredUuid $uuid): void;
}