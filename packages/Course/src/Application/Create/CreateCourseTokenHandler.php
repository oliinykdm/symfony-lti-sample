<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredUuid;

use CourseHub\Course\Application\CourseToken;
use CourseHub\Course\Application\CourseTokenWriter;


final class CreateCourseTokenHandler
{
    public function __construct(
        private CourseTokenWriter $courseTokenWriter,
    ) {}

    public function handle(CreateCourseToken $command): bool
    {
        $courseTokenToSave = CourseToken::generate(
            RequiredUuid::generate(),
            $command->getCourseId(),
            $command->getScope(),
            $command->getToken(),
            $command->getTimeCreated(),
            $command->getValidTo(),
            $command->getLastAccess()
        );

        $this->courseTokenWriter->create($courseTokenToSave);

        return true;
    }
}