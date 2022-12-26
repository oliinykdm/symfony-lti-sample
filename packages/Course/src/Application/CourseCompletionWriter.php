<?php declare(strict_types=1);

namespace CourseHub\Course\Application;

use CourseHub\Common\Domain\Types\RequiredUuid;

interface CourseCompletionWriter
{
    public function create(Completion $completion): void;
    public function update(Completion $completion): void;
    public function delete(RequiredUuid $uuid): void;
}