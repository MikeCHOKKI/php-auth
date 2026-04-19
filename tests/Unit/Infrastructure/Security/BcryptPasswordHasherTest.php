<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Security;

use App\Infrastructure\Security\BcryptPasswordHasher;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BcryptPasswordHasherTest extends TestCase
{
    private BcryptPasswordHasher $hasher;

    protected function setUp(): void
    {
        $this->hasher = new BcryptPasswordHasher();
    }

    public function testCanHashPassword(): void
    {
        $password = 'securepassword123';
        $hash = $this->hasher->hash($password);

        $this->assertNotEmpty($hash->getHash());
        $this->assertStringStartsWith('$argon2id$', $hash->getHash());
    }

    public function testCanVerifyCorrectPassword(): void
    {
        $password = 'securepassword123';
        $hash = $this->hasher->hash($password);

        $this->assertTrue($this->hasher->verify($password, $hash));
    }

    public function testFailsOnIncorrectPassword(): void
    {
        $password = 'securepassword123';
        $hash = $this->hasher->hash($password);

        $this->assertFalse($this->hasher->verify('wrongpassword', $hash));
    }

    public function testThrowsExceptionOnShortPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->hasher->hash('short');
    }
}
