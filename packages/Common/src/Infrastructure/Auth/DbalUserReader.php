<?php declare(strict_types=1);

namespace CourseHub\Common\Infrastructure\Auth;

use CourseHub\Common\Application\Auth\User;
use CourseHub\Common\Application\Auth\UserReader;
use CourseHub\Common\Domain\Types\RequiredText;
use CourseHub\Common\Domain\Types\RequiredUuid;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;


final class DbalUserReader implements UserReader
{
    public function __construct(
        private Connection $connection
    ) {}

    public function authorize(RequiredText $login, RequiredText $password): ?User
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('uuid', 'login', 'password', 'role');
        $qb->from('users');
        $qb->andWhere("login = {$qb->createNamedParameter($login->value(), Types::STRING)}");
        $qb->andWhere("password = {$qb->createNamedParameter($password->value(), Types::STRING)}");
        $row = $qb->executeQuery()->fetchAssociative();

        if (!$row) {
            return null;
        }

       return new User(
           RequiredUuid::fromString($row['uuid']),
           RequiredText::fromString($row['login']),
           RequiredText::fromString($row['password']),
           RequiredText::fromString($row['role']),
       );
    }
}