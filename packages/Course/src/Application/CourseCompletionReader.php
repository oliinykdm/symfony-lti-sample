<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseCompletionReader
{
    public function findByCourseAndUserId(RequiredUuid $courseId, RequiredUuid $userId): ?Completion;
    public function findById(RequiredUuid $uuid): ?Completion;
}