<?php declare(strict_types=1);

namespace CourseHub\Common\Application\Auth;


use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class Auth
{
    public function __construct(
        private string $login,
        private string $password,
    ) {
    }

    public static function generate(
        string $login,
        string $password,
    ): Auth {
        return new self(
            $login,
            $password,
        );
    }

    public function getLogin(): RequiredText
    {
        return RequiredText::fromString($this->login);
    }
    public function getPassword(): RequiredText
    {
        return RequiredText::fromString($this->password);
    }
}