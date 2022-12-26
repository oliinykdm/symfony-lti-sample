<?php declare(strict_types=1);

namespace CourseHub\Common\Application\Auth;

use Symfony\Component\HttpFoundation\RequestStack;


final class AuthHandler
{
    public function __construct(
        private UserReader $userReader,
        private RequestStack $requestStack
    ) {}

    public function handle(Auth $command): bool
    {

        $user = $this->userReader->authorize($command->getLogin(), $command->getPassword());
        if(!$user) {
            return false;
        }

        $session = $this->requestStack->getSession();

        $session->set('user', serialize($user));

        return true;
    }
}