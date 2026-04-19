<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\Permission;
use App\Domain\Repository\UserRepository;
use App\Domain\ValueObject\UserId;

class AuthorizationService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function canUserDo(UserId $userId, string $resource, string $action): bool
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return false;
        }

        return $user->hasPermissionByName($resource, $action);
    }

    public function canUser(UserId $userId, Permission $permission): bool
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return false;
        }

        return $user->hasPermission($permission);
    }

    public function getUserPermissions(UserId $userId): array
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return [];
        }

        $permissions = [];
        foreach ($user->getAllPermissions() as $permission) {
            $permissions[] = [
                'id' => $permission->getId(),
                'resource' => $permission->getResource(),
                'action' => $permission->getAction(),
                'identifier' => $permission->getIdentifier(),
            ];
        }

        return $permissions;
    }

    public function getUserRoles(UserId $userId): array
    {
        $user = $this->userRepository->findById($userId);

        if ($user === null) {
            return [];
        }

        $roles = [];
        foreach ($user->getRoles() as $role) {
            $roles[] = [
                'id' => $role->getId(),
                'name' => $role->getName(),
                'description' => $role->getDescription(),
            ];
        }

        return $roles;
    }
}
