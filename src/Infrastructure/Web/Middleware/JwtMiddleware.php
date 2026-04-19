<?php

declare(strict_types=1);

namespace App\Infrastructure\Web\Middleware;

use App\Application\Service\AuthenticationService;
use App\Domain\Service\Exception\JwtException;

class JwtMiddleware
{
    private AuthenticationService $authService;
    private array $excludedPaths;

    public function __construct(
        AuthenticationService $authService,
        array $excludedPaths = []
    ) {
        $this->authService = $authService;
        $this->excludedPaths = $excludedPaths;
    }

    public function handle(): void
    {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/';

        foreach ($this->excludedPaths as $excluded) {
            if (str_starts_with($path, $excluded)) {
                return;
            }
        }

        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $this->unauthorized('Missing or invalid Authorization header');
        }

        $token = $matches[1];

        try {
            $jwtToken = $this->authService->verifyAccessToken($token);

            if ($jwtToken->isExpired()) {
                $this->unauthorized('Token has expired');
            }

            $_SERVER['HTTP_X_USER_ID'] = $jwtToken->getSubject();
            $_SERVER['HTTP_X_USER_ROLES'] = json_encode($jwtToken->getRoles());
        } catch (JwtException $e) {
            $this->unauthorized($e->getMessage());
        }
    }

    private function unauthorized(string $message): void
    {
        http_response_code(401);
        header('Content-Type: application/json');
        header('WWW-Authenticate: Bearer');
        echo json_encode(['error' => $message]);
        exit;
    }
}
