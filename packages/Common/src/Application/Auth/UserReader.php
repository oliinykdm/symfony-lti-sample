<?php declare(strict_types=1);

namespace CourseHub\Common\Application\Auth;

use CourseHub\Common\Domain\Types\RequiredText;

interface UserReader
{
    public function authorize(RequiredText $login, RequiredText $password): ?User;
}