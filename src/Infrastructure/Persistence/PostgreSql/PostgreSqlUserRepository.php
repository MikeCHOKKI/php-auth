<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\PostgreSql;

use App\Domain\Entity\Role;
use App\Domain\Entity\User;
use App\Domain\Repository\UserRepository;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\UserId;
use PDO;

class PostgreSqlUserRepository implements UserRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(User $user): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (id, email, password_hash, created_at, updated_at)
             VALUES (:id, :email, :password_hash, :created_at, :updated_at)
             ON CONFLICT (id) DO UPDATE SET
                email = EXCLUDED.email,
                password_hash = EXCLUDED.password_hash,
                updated_at = EXCLUDED.updated_at'
        );

        $stmt->execute([
            ':id' => $user->getId()->getValue(),
            ':email' => $user->getEmail()->getValue(),
            ':password_hash' => $user->getPasswordHash()->getHash(),
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ':updated_at' => $user->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);

        $this->syncRoles($user);
    }

    public function findById(UserId $id): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = :id');
        $stmt->execute([':id' => $id->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function findByEmail(Email $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email->getValue()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrateUser($row);
    }

    public function exists(Email $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM users WHERE email = :email');
        $stmt->execute([':email' => $email->getValue()]);

        return $stmt->fetch() !== false;
    }

    public function delete(UserId $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id->getValue()]);
    }

    private function hydrateUser(array $row): User
    {
        return new User(
            new UserId($row['id']),
            new Email($row['email']),
            new PasswordHash($row['password_hash']),
            [],
            new \DateTimeImmutable($row['created_at']),
            new \DateTimeImmutable($row['updated_at'])
        );
    }

    private function syncRoles(User $user): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM user_roles WHERE user_id = :user_id');
        $stmt->execute([':user_id' => $user->getId()->getValue()]);

        foreach ($user->getRoles() as $role) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO user_roles (user_id, role_id, assigned_at)
                 VALUES (:user_id, :role_id, NOW())'
            );
            $stmt->execute([
                ':user_id' => $user->getId()->getValue(),
                ':role_id' => $role->getId(),
            ]);
        }
    }
}
