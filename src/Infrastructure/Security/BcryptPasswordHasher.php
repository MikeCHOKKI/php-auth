<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Service\PasswordHasher;
use App\Domain\ValueObject\PasswordHash;

final class BcryptPasswordHasher implements PasswordHasher
{
    private const MEMORY_COST = 65536;
    private const TIME_COST = 4;
    private const THREADS = 3;

    public function hash(string $plainPassword): PasswordHash
    {
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }

        $hash = password_hash($plainPassword, PASSWORD_ARGON2ID, [
            'memory_cost' => self::MEMORY_COST,
            'time_cost' => self::TIME_COST,
            'threads' => self::THREADS,
        ]);

        if (!$hash) {
            throw new \RuntimeException('Failed to hash password');
        }

        return new PasswordHash($hash);
    }

    public function verify(string $plainPassword, PasswordHash $hash): bool
    {
        return password_verify($plainPassword, $hash->getHash());
    }
}
