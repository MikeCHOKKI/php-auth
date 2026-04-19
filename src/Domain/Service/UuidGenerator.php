<?php

declare(strict_types=1);

namespace App\Domain\Service;

interface UuidGenerator
{
    public function generate(): string;
}
