<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\RefreshToken;
use App\Domain\Repository\RefreshTokenRepository;
use App\Domain\Service\Exception\AuthenticationException;
use App\Domain\ValueObject\UserId;

class TokenRotationService
{
    private RefreshTokenRepository $refreshTokenRepository;

    public function __construct(RefreshTokenRepository $refreshTokenRepository)
    {
        $this->refreshTokenRepository = $refreshTokenRepository;
    }

    public function detectReuse(RefreshToken $token): bool
    {
        return !$token->isValid() || $token->isReplaced();
    }

    public function handleReuse(RefreshToken $token): void
    {
        $this->refreshTokenRepository->revokeAllUserTokens($token->getUserId());
        throw AuthenticationException::tokenReuseDetected();
    }

    public function rotateToken(
        RefreshToken $oldToken,
        string $newTokenString,
        int $ttlSeconds
    ): array {
        if ($this->detectReuse($oldToken)) {
            $this->handleReuse($oldToken);
        }

        $oldToken->revoke();

        $newToken = RefreshToken::create(
            $newTokenString,
            $oldToken->getUserId(),
            $ttlSeconds
        );

        $oldToken->replaceWith($newToken->getId());

        $this->refreshTokenRepository->save($oldToken);
        $this->refreshTokenRepository->save($newToken);

        return [
            'newToken' => $newToken,
            'tokenString' => $newTokenString,
        ];
    }

    public function createNewToken(UserId $userId, string $tokenString, int $ttlSeconds): RefreshToken
    {
        $token = RefreshToken::create($tokenString, $userId, $ttlSeconds);
        $this->refreshTokenRepository->save($token);

        return $token;
    }
}
