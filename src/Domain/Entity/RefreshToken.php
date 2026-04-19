<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\ValueObject\UserId;

class RefreshToken
{
    private string $id;
    private string $tokenHash;
    private UserId $userId;
    private \DateTimeImmutable $expiresAt;
    private \DateTimeImmutable $createdAt;
    private ?string $replacedBy = null;
    private ?\DateTimeImmutable $revokedAt = null;
    private bool $isRevoked = false;

    public function __construct(
        string $id,
        string $tokenHash,
        UserId $userId,
        \DateTimeImmutable $expiresAt,
        \DateTimeImmutable $createdAt,
        ?string $replacedBy = null,
        ?\DateTimeImmutable $revokedAt = null,
        bool $isRevoked = false
    ) {
        $this->id = $id;
        $this->tokenHash = $tokenHash;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
        $this->createdAt = $createdAt;
        $this->replacedBy = $replacedBy;
        $this->revokedAt = $revokedAt;
        $this->isRevoked = $isRevoked;
    }

    public static function create(
        string $tokenString,
        UserId $userId,
        int $ttlSeconds
    ): self {
        $id = self::generateUuid();
        $tokenHash = hash('sha256', $tokenString);
        $expiresAt = (new \DateTimeImmutable())->modify("+{$ttlSeconds} seconds");

        return new self($id, $tokenHash, $userId, $expiresAt, new \DateTimeImmutable());
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTokenHash(): string
    {
        return $this->tokenHash;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getReplacedBy(): ?string
    {
        return $this->replacedBy;
    }

    public function getRevokedAt(): ?\DateTimeImmutable
    {
        return $this->revokedAt;
    }

    public function isRevoked(): bool
    {
        return $this->isRevoked;
    }

    public function isExpired(): bool
    {
        return new \DateTimeImmutable() > $this->expiresAt;
    }

    public function isReplaced(): bool
    {
        return $this->replacedBy !== null;
    }

    public function isValid(): bool
    {
        return !$this->isRevoked && !$this->isExpired();
    }

    public function revoke(): void
    {
        $this->isRevoked = true;
        $this->revokedAt = new \DateTimeImmutable();
    }

    public function replaceWith(string $newTokenId): void
    {
        $this->replacedBy = $newTokenId;
    }

    public function belongsTo(UserId $userId): bool
    {
        return $this->userId->equals($userId);
    }

    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    private static function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        );
    }
}
