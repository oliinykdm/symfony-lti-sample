<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Update;

use CourseHub\Course\Application\Course;
use CourseHub\Course\Application\CourseValidator;
use CourseHub\Course\Application\CourseWriter;

final class UpdateCourseHandler
{
    public function __construct(
        private CourseWriter $messageWriter,
        private CourseValidator $courseValidator
    ) {}

    public function handle(UpdateCourse $command): bool
    {
        $courseToSave = Course::generate(
            $command->getUuid(),
            $command->getToolName(),
            $command->getToolUrl(),
            $command->getInitiateLoginUrl(),
            $command->getJwksUrl(),
            $command->getDeepLinkingUrl(),
            null,
            null
        );

        $this->courseValidator->validateUpdate($command);

        if($this->courseValidator->getErrors()) {
            return false;
        }
        else {
            $this->messageWriter->update($courseToSave);
        }

        return true;
    }
}