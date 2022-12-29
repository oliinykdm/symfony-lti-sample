<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Course;

use CourseHub\Course\Application\CourseValidator;
use CourseHub\Course\Application\CourseWriter;


final class CreateCourseHandler
{
    public function __construct(
        private CourseWriter $messageWriter,
        private CourseValidator $courseValidator,
        private CreateCourseTokenHandler $createCourseTokenHandler,
    ) {}

    public function handle(CreateCourse $command): bool
    {
        $courseToSave = Course::generate(
            $command->getId(),
            $command->getToolName(),
            $command->getToolUrl(),
            $command->getInitiateLoginUrl(),
            $command->getJwksUrl(),
            $command->getDeepLinkingUrl(),
            RequiredUuid::generate(),
            RequiredUuid::generate(),
            null
        );

        $this->courseValidator->validateCreate($command);

        if($this->courseValidator->getErrors()) {
            return false;
        }
        else {
            $this->messageWriter->create($courseToSave);
            $this->createCourseTokenHandler->handle(
                new CreateCourseToken(
                    $command->getId()->value(),
                    '',
                    RequiredUuid::generate()->value(),
                    '',
                    '',
                    ''
                )
            );
        }

        return true;
    }
}