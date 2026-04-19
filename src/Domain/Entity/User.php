<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Service\PasswordHasher;
use App\Domain\Service\UuidGenerator;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\PasswordHash;
use App\Domain\ValueObject\UserId;

class User
{
    private UserId $id;
    private Email $email;
    private PasswordHash $passwordHash;
    private array $roles;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        UserId $id,
        Email $email,
        PasswordHash $passwordHash,
        array $roles = [],
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
        $this->roles = $roles;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public static function create(
        string $email,
        string $plainPassword,
        PasswordHasher $passwordHasher,
        ?UuidGenerator $uuidGenerator = null
    ): self {
        $id = new UserId($uuidGenerator ? $uuidGenerator->generate() : self::generateUuid());
        $emailVo = new Email($email);
        $passwordHash = $passwordHasher->hash($plainPassword);

        return new self($id, $emailVo, $passwordHash);
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getPasswordHash(): PasswordHash
    {
        return $this->passwordHash;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function verifyPassword(string $password, PasswordHasher $hasher): bool
    {
        return $this->passwordHash->verify($password, $hasher);
    }

    public function assignRole(Role $role): void
    {
        $this->roles[$role->getId()] = $role;
    }

    public function removeRole(Role $role): void
    {
        unset($this->roles[$role->getId()]);
    }

    public function hasRole(string $roleName): bool
    {
        foreach ($this->roles as $role) {
            if ($role->getName() === $roleName) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(Permission $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermissionByName(string $resource, string $action): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermissionByName($resource, $action)) {
                return true;
            }
        }

        return false;
    }

    public function getAllPermissions(): array
    {
        $permissions = [];

        foreach ($this->roles as $role) {
            foreach ($role->getPermissions() as $permission) {
                $permissions[$permission->getIdentifier()] = $permission;
            }
        }

        return $permissions;
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
