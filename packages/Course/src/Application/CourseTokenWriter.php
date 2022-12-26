<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseTokenWriter
{
    public function create(CourseToken $courseToken): void;
    public function update(CourseToken $courseToken): void;
    public function delete(RequiredUuid $uuid): void;
}