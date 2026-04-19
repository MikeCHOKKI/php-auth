<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Service\AuthorizationService;
use App\Domain\ValueObject\UserId;

class UserController
{
    private AuthorizationService $authzService;

    public function __construct(AuthorizationService $authzService)
    {
        $this->authzService = $authzService;
    }

    public function me(): void
    {
        $userId = $this->getCurrentUserId();

        if (!$userId) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $permissions = $this->authzService->getUserPermissions($userId);
        $roles = $this->authzService->getUserRoles($userId);

        $this->jsonResponse([
            'id' => $userId->getValue(),
            'permissions' => $permissions,
            'roles' => $roles,
        ]);
    }

    public function permissions(): void
    {
        $userId = $this->getCurrentUserId();

        if (!$userId) {
            $this->jsonResponse(['error' => 'Unauthorized'], 401);
            return;
        }

        $permissions = $this->authzService->getUserPermissions($userId);

        $this->jsonResponse([
            'permissions' => $permissions,
            'count' => count($permissions),
        ]);
    }

    private function getCurrentUserId(): ?UserId
    {
        $userId = $_SERVER['HTTP_X_USER_ID'] ?? null;

        if (!$userId) {
            return null;
        }

        try {
            return new UserId($userId);
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }
}
