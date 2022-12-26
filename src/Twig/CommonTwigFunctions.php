<?php declare(strict_types=1);

namespace CourseHub\Twig;

use CourseHub\Common\Domain\Types\RequiredUuid;
use CourseHub\Course\Application\Course;
use Ramsey\Uuid\Uuid;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class CommonTwigFunctions extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('binaryUuidToString', [$this, 'binaryUuidToString']),
            new TwigFilter('toString', [$this, 'toString']),
        ];
    }

    public function binaryUuidToString(string $binary): string
    {
        return RequiredUuid::fromString($binary)->value();
    }

    public function toString(object $object): string
    {
        return $object->value();
    }
}