<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Email;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class EmailTest extends TestCase
{
    public function testCanCreateValidEmail(): void
    {
        $email = new Email('test@example.com');

        $this->assertEquals('test@example.com', $email->getValue());
    }

    public function testThrowsExceptionForInvalidEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('invalid-email');
    }

    public function testThrowsExceptionForEmptyEmail(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Email('');
    }

    public function testCanExtractLocalPart(): void
    {
        $email = new Email('user@example.com');

        $this->assertEquals('user', $email->getLocalPart());
    }

    public function testCanExtractDomain(): void
    {
        $email = new Email('user@example.com');

        $this->assertEquals('example.com', $email->getDomain());
    }

    public function testEqualsReturnsTrueForSameEmail(): void
    {
        $email1 = new Email('test@example.com');
        $email2 = new Email('test@example.com');

        $this->assertTrue($email1->equals($email2));
    }

    public function testEqualsIsCaseInsensitive(): void
    {
        $email1 = new Email('Test@Example.COM');
        $email2 = new Email('test@example.com');

        $this->assertTrue($email1->equals($email2));
    }
}
