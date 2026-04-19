<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use InvalidArgumentException;

final readonly class Email
{
    public function __construct(
        private string $value
    ) {
        if (empty($this->value)) {
            throw new InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid email format');
        }
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return strtolower($this->value) === strtolower($other->value);
    }

    public function getLocalPart(): string
    {
        return substr($this->value, 0, (int)strpos($this->value, '@'));
    }

    public function getDomain(): string
    {
        return substr($this->value, (int)strpos($this->value, '@') + 1);
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
