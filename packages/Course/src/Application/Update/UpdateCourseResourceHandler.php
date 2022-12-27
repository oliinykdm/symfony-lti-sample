<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Update;

use CourseHub\Course\Application\Course;
use CourseHub\Course\Application\CourseReader;
use CourseHub\Course\Application\CourseResource;
use CourseHub\Course\Application\CourseResourceReader;
use CourseHub\Course\Application\CourseResourceValidator;
use CourseHub\Course\Application\CourseResourceWriter;
use CourseHub\Course\Application\CourseValidator;
use CourseHub\Course\Application\CourseWriter;

final class UpdateCourseResourceHandler
{
    public function __construct(
        private CourseResourceWriter $courseResourceWriter,
        private CourseResourceReader $courseResourceReader,
        private CourseResourceValidator $courseResourceValidator
    ) {}

    public function handle(UpdateCourseResource $command): bool
    {
        $resource = $this->courseResourceReader->findById($command->getId());
        $resourceToSave = CourseResource::generate(
            $command->getId(),
            $resource->getCourseId(),
            $resource->getType(),
            $command->getTitle(),
            $command->getText(),
            $resource->getUrl(),
            $command->getResourceId(),
            $resource->getDump(),
        );

        $this->courseResourceValidator->validateUpdate($command);

        if($this->courseResourceValidator->getErrors()) {
            return false;
        }
        else {
            $this->courseResourceWriter->update($resourceToSave);
        }

        return true;
    }
}