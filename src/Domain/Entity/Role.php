<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Role
{
    private string $id;
    private string $name;
    private ?string $description;
    private array $permissions;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $id,
        string $name,
        ?string $description = null,
        array $permissions = [],
        ?\DateTimeImmutable $createdAt = null,
        ?\DateTimeImmutable $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->permissions = $permissions;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = $updatedAt ?? new \DateTimeImmutable();
    }

    public static function create(
        string $name,
        ?string $description = null,
        UuidGenerator $uuidGenerator = null
    ): self {
        $id = $uuidGenerator ? $uuidGenerator->generate() : self::generateUuid();

        return new self($id, $name, $description);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function grantPermission(Permission $permission): void
    {
        $this->permissions[$permission->getId()] = $permission;
    }

    public function revokePermission(Permission $permission): void
    {
        unset($this->permissions[$permission->getId()]);
    }

    public function hasPermission(Permission $permission): bool
    {
        return isset($this->permissions[$permission->getId()]);
    }

    public function hasPermissionByName(string $resource, string $action): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->matches($resource, $action)) {
                return true;
            }
        }

        return false;
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
