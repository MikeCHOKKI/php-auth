<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Domain\Service\Exception\JwtException;
use App\Domain\ValueObject\JwtToken;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class Rs256JwtSigner
{
    private const ALGORITHM = 'RS256';
    private string $privateKeyPath;
    private string $publicKeyPath;

    public function __construct(
        string $privateKeyPath,
        string $publicKeyPath
    ) {
        $this->privateKeyPath = $privateKeyPath;
        $this->publicKeyPath = $publicKeyPath;
    }

    public function sign(array $claims, int $ttlSeconds): JwtToken
    {
        $privateKey = $this->loadPrivateKey();

        $claims['iat'] = time();
        $claims['exp'] = time() + $ttlSeconds;
        $claims['type'] = 'access';

        try {
            $token = JWT::encode($claims, $privateKey, self::ALGORITHM);
            return new JwtToken($token, $claims);
        } catch (\Exception $e) {
            throw new JwtException('Failed to sign JWT: ' . $e->getMessage(), 0, $e);
        }
    }

    public function verify(string $tokenString): JwtToken
    {
        $publicKey = $this->loadPublicKey();

        try {
            $decoded = JWT::decode($tokenString, new Key($publicKey, self::ALGORITHM));
            $claims = (array) $decoded;

            return new JwtToken($tokenString, $claims);
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new JwtException('Token has expired', 0, $e);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new JwtException('Invalid token signature', 0, $e);
        } catch (\Exception $e) {
            throw new JwtException('Failed to verify JWT: ' . $e->getMessage(), 0, $e);
        }
    }

    public function generateRefreshToken(string $userId): string
    {
        return bin2hex(random_bytes(32)) . bin2hex(random_bytes(32));
    }

    private function loadPrivateKey(): string
    {
        if (!file_exists($this->privateKeyPath)) {
            throw new JwtException('Private key not found: ' . $this->privateKeyPath);
        }

        $key = file_get_contents($this->privateKeyPath);

        if ($key === false) {
            throw new JwtException('Failed to read private key');
        }

        return $key;
    }

    private function loadPublicKey(): string
    {
        if (!file_exists($this->publicKeyPath)) {
            throw new JwtException('Public key not found: ' . $this->publicKeyPath);
        }

        $key = file_get_contents($this->publicKeyPath);

        if ($key === false) {
            throw new JwtException('Failed to read public key');
        }

        return $key;
    }
}
