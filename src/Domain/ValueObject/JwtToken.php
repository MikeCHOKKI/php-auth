<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final readonly class JwtToken
{
    /**
     * @param string $token Le token JWT encodé
     * @param array<string, mixed> $claims Les claims décodés
     */
    public function __construct(
        private string $token,
        private array $claims = []
    ) {
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getClaims(): array
    {
        return $this->claims;
    }

    public function getSubject(): ?string
    {
        return $this->claims['sub'] ?? null;
    }

    public function getExpiration(): ?int
    {
        return $this->claims['exp'] ?? null;
    }

    public function getIssuedAt(): ?int
    {
        return $this->claims['iat'] ?? null;
    }

    public function getRoles(): array
    {
        return $this->claims['roles'] ?? [];
    }

    public function isExpired(): bool
    {
        $exp = $this->getExpiration();
        if ($exp === null) {
            return false;
        }

        return time() > $exp;
    }

    public function __toString(): string
    {
        return $this->token;
    }
}
