<?php

declare(strict_types=1);

namespace App\Domain\Service\Exception;

use Exception;

class AuthenticationException extends Exception
{
    public static function invalidCredentials(): self
    {
        return new self('Invalid email or password');
    }

    public static function userNotFound(): self
    {
        return new self('User not found');
    }

    public static function tokenReuseDetected(): self
    {
        return new self('Token reuse detected - all sessions revoked for security');
    }

    public static function tokenExpired(): self
    {
        return new self('Token has expired');
    }

    public static function tokenInvalid(): self
    {
        return new self('Token is invalid');
    }
}
