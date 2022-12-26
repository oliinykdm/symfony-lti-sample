<?php declare(strict_types=1);

namespace CourseHub\Course\Application\Create;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Completion;
use CourseHub\Course\Application\CourseCompletionWriter;


final class CreateCompletionHandler
{
    public function __construct(
        private CourseCompletionWriter $courseCompletionWriter,
    ) {}

    public function handle(CreateCompletion $command): bool
    {
        $completion = Completion::generate(
            RequiredUuid::generate(),
            $command->getUserId(),
            $command->getCourseId(),
            $command->getScore(),
            $command->getCompletionDate(),
            $command->getDump(),
        );

        $this->courseCompletionWriter->create($completion);

        return true;
    }
}