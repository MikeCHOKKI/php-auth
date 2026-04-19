<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;
use Random\RandomException;

final readonly class UserId
{
    public function __construct(
        private string $value
    ) {
        if (empty($this->value)) {
            throw new InvalidArgumentException('User ID cannot be empty');
        }

        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $this->value)) {
            throw new InvalidArgumentException('Invalid UUID format for User ID');
        }
    }

    /**
     * @throws RandomException
     */
    public static function generate(): self
    {
        return new self(sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0x0fff) | 0x4000,
            random_int(0, 0x3fff) | 0x8000,
            random_int(0, 0xffff),
            random_int(0, 0xffff),
            random_int(0, 0xffff)
        ));
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
