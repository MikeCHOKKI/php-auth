<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Controller;

use App\Application\Service\AuthenticationService;
use App\Domain\Service\Exception\AuthenticationException;
use JsonException;

class AuthController
{
    private AuthenticationService $authService;

    public function __construct(AuthenticationService $authService)
    {
        $this->authService = $authService;
    }

    public function login(): void
    {
        try {
            $data = $this->getJsonInput();

            if (empty($data['email']) || empty($data['password'])) {
                $this->jsonResponse(['error' => 'Email and password are required'], 400);
                return;
            }

            $result = $this->authService->login($data['email'], $data['password']);

            $this->jsonResponse($result);
        } catch (AuthenticationException $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 401);
        } catch (JsonException $e) {
            $this->jsonResponse(['error' => 'Invalid JSON'], 400);
        }
    }

    public function refresh(): void
    {
        try {
            $data = $this->getJsonInput();

            if (empty($data['refreshToken'])) {
                $this->jsonResponse(['error' => 'Refresh token is required'], 400);
                return;
            }

            $result = $this->authService->refreshToken($data['refreshToken']);

            $this->jsonResponse($result);
        } catch (AuthenticationException $e) {
            $this->jsonResponse(['error' => $e->getMessage()], 401);
        } catch (JsonException $e) {
            $this->jsonResponse(['error' => 'Invalid JSON'], 400);
        }
    }

    public function logout(): void
    {
        $this->authService->logout($_SESSION['user_id'] ?? throw new AuthenticationException('Not authenticated'));
        $this->jsonResponse(['message' => 'Logged out successfully']);
    }

    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        if ($input === false) {
            return [];
        }
        return json_decode($input, true, 512, JSON_THROW_ON_ERROR) ?? [];
    }

    private function jsonResponse(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_THROW_ON_ERROR);
    }
}
