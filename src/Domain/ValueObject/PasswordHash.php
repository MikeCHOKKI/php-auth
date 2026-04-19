<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Service\PasswordHasher;

final readonly class PasswordHash
{
    public function __construct(
        private string $hash
    ) {
        if (empty($this->hash)) {
            throw new \InvalidArgumentException('Password hash cannot be empty');
        }
    }

    public static function fromPlainPassword(
        string $plainPassword,
        PasswordHasher $hasher
    ): self {
        return $hasher->hash($plainPassword);
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function verify(string $plainPassword, PasswordHasher $hasher): bool
    {
        return $hasher->verify($plainPassword, $this);
    }

    public function __toString(): string
    {
        return $this->hash;
    }
}
