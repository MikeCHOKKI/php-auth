<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final readonly class UserId
{
    public function __construct(
        private string $value
    ) {
        if (empty($this->value)) {
            throw new InvalidArgumentException('User ID cannot be empty');
        }

        if (!preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->value)) {
            throw new InvalidArgumentException('Invalid UUID format for User ID');
        }
    }

    public static function generate(): self
    {
        return new self(uuid_create(UUID_TYPE_RANDOM));
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
