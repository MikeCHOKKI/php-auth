<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Role;

interface RoleRepository
{
    public function save(Role $role): void;

    public function findById(string $id): ?Role;

    public function findByName(string $name): ?Role;

    public function findAll(): array;

    public function delete(string $id): void;
}
