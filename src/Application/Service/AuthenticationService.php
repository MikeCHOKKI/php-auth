<?php

declare(strict_types=1);

namespace App\Application\Service;

use App\Domain\Entity\User;
use App\Domain\Entity\RefreshToken;
use App\Domain\Repository\RefreshTokenRepository;
use App\Domain\Repository\UserRepository;
use App\Domain\Service\Exception\AuthenticationException;
use App\Domain\Service\Exception\JwtException;
use App\Domain\Service\PasswordHasher;
use App\Domain\ValueObject\Email;
use App\Domain\ValueObject\JwtToken;
use App\Domain\ValueObject\UserId;
use App\Infrastructure\Security\Rs256JwtSigner;

class AuthenticationService
{
    private UserRepository $userRepository;
    private RefreshTokenRepository $refreshTokenRepository;
    private PasswordHasher $passwordHasher;
    private Rs256JwtSigner $jwtSigner;
    private TokenRotationService $tokenRotationService;
    private int $accessTokenTtl;
    private int $refreshTokenTtl;

    public function __construct(
        UserRepository $userRepository,
        RefreshTokenRepository $refreshTokenRepository,
        PasswordHasher $passwordHasher,
        Rs256JwtSigner $jwtSigner,
        TokenRotationService $tokenRotationService,
        int $accessTokenTtl = 900,
        int $refreshTokenTtl = 604800
    ) {
        $this->userRepository = $userRepository;
        $this->refreshTokenRepository = $refreshTokenRepository;
        $this->passwordHasher = $passwordHasher;
        $this->jwtSigner = $jwtSigner;
        $this->tokenRotationService = $tokenRotationService;
        $this->accessTokenTtl = $accessTokenTtl;
        $this->refreshTokenTtl = $refreshTokenTtl;
    }

    /**
     * @throws AuthenticationException
     */
    public function login(string $email, string $password): array
    {
        $emailVo = new Email($email);
        $user = $this->userRepository->findByEmail($emailVo);

        if ($user === null) {
            throw AuthenticationException::invalidCredentials();
        }

        if (!$user->verifyPassword($password, $this->passwordHasher)) {
            throw AuthenticationException::invalidCredentials();
        }

        $accessToken = $this->generateAccessToken($user);
        $refreshTokenString = $this->jwtSigner->generateRefreshToken($user->getId()->getValue());

        $this->tokenRotationService->createNewToken(
            $user->getId(),
            $refreshTokenString,
            $this->refreshTokenTtl
        );

        return [
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $refreshTokenString,
            'expiresIn' => $this->accessTokenTtl,
            'tokenType' => 'Bearer',
        ];
    }

    /**
     * @throws AuthenticationException
     */
    public function refreshToken(string $refreshTokenString): array
    {
        $tokenHash = RefreshToken::hashToken($refreshTokenString);
        $refreshToken = $this->refreshTokenRepository->findByTokenHash($tokenHash);

        if ($refreshToken === null) {
            throw AuthenticationException::tokenInvalid();
        }

        if ($this->tokenRotationService->detectReuse($refreshToken)) {
            $this->tokenRotationService->handleReuse($refreshToken);
        }

        $newRefreshTokenString = $this->jwtSigner->generateRefreshToken(
            $refreshToken->getUserId()->getValue()
        );

        $rotationResult = $this->tokenRotationService->rotateToken(
            $refreshToken,
            $newRefreshTokenString,
            $this->refreshTokenTtl
        );

        $user = $this->userRepository->findById($refreshToken->getUserId());

        if ($user === null) {
            throw AuthenticationException::userNotFound();
        }

        $accessToken = $this->generateAccessToken($user);

        return [
            'accessToken' => $accessToken->getToken(),
            'refreshToken' => $rotationResult['tokenString'],
            'expiresIn' => $this->accessTokenTtl,
            'tokenType' => 'Bearer',
        ];
    }

    public function logout(UserId $userId): void
    {
        $this->refreshTokenRepository->revokeAllUserTokens($userId);
    }

    /**
     * @throws JwtException|JwtException
     */
    public function verifyAccessToken(string $tokenString): JwtToken
    {
        return $this->jwtSigner->verify($tokenString);
    }

    private function generateAccessToken(User $user): JwtToken
    {
        $roles = [];
        foreach ($user->getRoles() as $role) {
            $roles[] = $role->getName();
        }

        $claims = [
            'sub' => $user->getId()->getValue(),
            'email' => $user->getEmail()->getValue(),
            'roles' => $roles,
        ];

        return $this->jwtSigner->sign($claims, $this->accessTokenTtl);
    }
}