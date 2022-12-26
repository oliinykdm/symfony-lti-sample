<?php declare(strict_types=1);

namespace CourseHub\Common\Application\Auth;


use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;

final class User
{
    public function __construct(
        private RequiredUuid $uuid,
        private RequiredText $login,
        private RequiredText $password,
        private RequiredText $role,
    ) {
    }

    public static function generate(
        RequiredUuid $uuid,
        RequiredText $login,
        RequiredText $password,
        RequiredText $role,
    ): User {
        return new self(
            $uuid,
            $login,
            $password,
            $role
        );
    }

    public function getId(): RequiredUuid
    {
        return $this->uuid;
    }
    public function getLogin(): RequiredText
    {
        return $this->login;
    }
    public function getPassword(): RequiredText
    {
        return $this->password;
    }
    public function getRole(): RequiredText
    {
        return $this->role;
    }
}