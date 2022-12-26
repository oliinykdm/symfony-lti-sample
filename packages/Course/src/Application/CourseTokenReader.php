<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseTokenReader
{
    public function findByCourseId(RequiredUuid $courseId): ?CourseToken;
}