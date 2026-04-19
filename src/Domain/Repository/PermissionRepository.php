<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Permission;

interface PermissionRepository
{
    public function save(Permission $permission): void;

    public function findById(string $id): ?Permission;

    public function findByResourceAndAction(string $resource, string $action): ?Permission;

    public function findAll(): array;

    public function findByUserId(string $userId): array;

    public function delete(string $id): void;
}
