<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredUuid;

use CourseHub\Course\Application\CourseResource;
use CourseHub\Course\Application\CourseResourceWriter;

final class CreateCourseResourceHandler
{
    public function __construct(
        private CourseResourceWriter $courseResourceWriter,
    ) {}

    public function handle(CreateCourseResource $command): bool
    {
        $resource = CourseResource::generate(
            RequiredUuid::generate(),
            $command->getCourseId(),
            $command->getType(),
            $command->getTitle(),
            $command->getText(),
            $command->getUrl(),
            $command->getResourceId(),
            $command->getDump()
        );

        $this->courseResourceWriter->create($resource);

        return true;
    }
}