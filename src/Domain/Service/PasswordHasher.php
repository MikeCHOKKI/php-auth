<?php

declare(strict_types=1);

namespace App\Domain\Service;

use App\Domain\ValueObject\PasswordHash;

interface PasswordHasher
{
    public function hash(string $plainPassword): PasswordHash;

    public function verify(string $plainPassword, PasswordHash $hash): bool;
}
