<?php declare(strict_types=1);

namespace CourseHub\Common\Application\Auth;

use Symfony\Component\HttpFoundation\RequestStack;


final class AuthHelper
{
    public function __construct(
        private RequestStack $requestStack,
        private AuthHandler $authHandler,
    ) {}

    public function getUser(): ?User
    {
        $session = $this->requestStack->getSession();
        if(!$session->get('user')) {
            return null;
        }
        return unserialize($session->get('user'));

    }

    public function logout(): void {
        $session = $this->requestStack->getSession();
        $session->set('user', null);
    }

    public function login($login, $password): bool {
        return $this->authHandler->handle(
            new Auth(
                $login,
                $password
            )
        );
    }
}