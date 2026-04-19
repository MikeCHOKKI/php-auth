<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\UserId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UserIdTest extends TestCase
{
    public function testCanCreateValidUserId(): void
    {
        $id = new UserId('550e8400-e29b-41d4-a716-446655440000');

        $this->assertEquals('550e8400-e29b-41d4-a716-446655440000', $id->getValue());
    }

    public function testThrowsExceptionForEmptyId(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserId('');
    }

    public function testThrowsExceptionForInvalidUuid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new UserId('not-a-valid-uuid');
    }

    public function testCanGenerateNewId(): void
    {
        $id = UserId::generate();

        $this->assertNotEmpty($id->getValue());
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $id->getValue()
        );
    }

    public function testEqualsReturnsTrueForSameId(): void
    {
        $id1 = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $id2 = new UserId('550e8400-e29b-41d4-a716-446655440000');

        $this->assertTrue($id1->equals($id2));
    }

    public function testEqualsReturnsFalseForDifferentIds(): void
    {
        $id1 = new UserId('550e8400-e29b-41d4-a716-446655440000');
        $id2 = new UserId('550e8400-e29b-41d4-a716-446655440001');

        $this->assertFalse($id1->equals($id2));
    }
}
