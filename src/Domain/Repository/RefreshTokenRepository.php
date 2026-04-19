<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\RefreshToken;
use App\Domain\ValueObject\UserId;

interface RefreshTokenRepository
{
    public function save(RefreshToken $token): void;

    public function findByTokenHash(string $hash): ?RefreshToken;

    public function findById(string $id): ?RefreshToken;

    public function revokeAllUserTokens(UserId $userId): void;

    public function deleteExpiredTokens(): void;

    public function delete(string $id): void;
}
