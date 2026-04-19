<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\PostgreSql;

use App\Domain\Entity\RefreshToken;
use App\Domain\Repository\RefreshTokenRepository;
use App\Domain\ValueObject\UserId;
use PDO;

class PostgreSqlRefreshTokenRepository implements RefreshTokenRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function save(RefreshToken $token): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO refresh_tokens (
                id, user_id, token_hash, expires_at, created_at,
                replaced_by, revoked_at, is_revoked
             ) VALUES (
                :id, :user_id, :token_hash, :expires_at, :created_at,
                :replaced_by, :revoked_at, :is_revoked
             )
             ON CONFLICT (id) DO UPDATE SET
                replaced_by = EXCLUDED.replaced_by,
                revoked_at = EXCLUDED.revoked_at,
                is_revoked = EXCLUDED.is_revoked'
        );

        $stmt->execute([
            ':id' => $token->getId(),
            ':user_id' => $token->getUserId()->getValue(),
            ':token_hash' => $token->getTokenHash(),
            ':expires_at' => $token->getExpiresAt()->format('Y-m-d H:i:s'),
            ':created_at' => $token->getCreatedAt()->format('Y-m-d H:i:s'),
            ':replaced_by' => $token->getReplacedBy(),
            ':revoked_at' => $token->getRevokedAt()?->format('Y-m-d H:i:s'),
            ':is_revoked' => $token->isRevoked() ? 't' : 'f',
        ]);
    }

    public function findByTokenHash(string $hash): ?RefreshToken
    {
        $stmt = $this->pdo->prepare('SELECT * FROM refresh_tokens WHERE token_hash = :hash');
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrateToken($row);
    }

    public function findById(string $id): ?RefreshToken
    {
        $stmt = $this->pdo->prepare('SELECT * FROM refresh_tokens WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return $this->hydrateToken($row);
    }

    public function revokeAllUserTokens(UserId $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE refresh_tokens
             SET is_revoked = true, revoked_at = NOW()
             WHERE user_id = :user_id'
        );
        $stmt->execute([':user_id' => $userId->getValue()]);
    }

    public function deleteExpiredTokens(): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM refresh_tokens WHERE expires_at < NOW() OR is_revoked = true'
        );
        $stmt->execute();
    }

    public function delete(string $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM refresh_tokens WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    private function hydrateToken(array $row): RefreshToken
    {
        return new RefreshToken(
            $row['id'],
            $row['token_hash'],
            new UserId($row['user_id']),
            new \DateTimeImmutable($row['expires_at']),
            new \DateTimeImmutable($row['created_at']),
            $row['replaced_by'],
            $row['revoked_at'] ? new \DateTimeImmutable($row['revoked_at']) : null,
            $row['is_revoked'] === 't' || $row['is_revoked'] === true
        );
    }
}
