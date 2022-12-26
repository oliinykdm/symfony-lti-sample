<?php declare(strict_types=1);

namespace CourseHub\Lti\Application;

use CourseHub\Course\Application\Course;

interface AbstractLtiConnector
{
    public function connect(Course $course): void;
}