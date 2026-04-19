<?php

declare(strict_types=1);

namespace App\Domain\Entity;

class Permission
{
    private string $id;
    private string $resource;
    private string $action;
    private ?string $description;
    private \DateTimeImmutable $createdAt;

    public function __construct(
        string $id,
        string $resource,
        string $action,
        ?string $description = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->id = $id;
        $this->resource = $resource;
        $this->action = $action;
        $this->description = $description;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public static function create(
        string $resource,
        string $action,
        ?string $description = null
    ): self {
        return new self(
            uuid_create(UUID_TYPE_RANDOM),
            $resource,
            $action,
            $description
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getResource(): string
    {
        return $this->resource;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function matches(string $resource, string $action): bool
    {
        return $this->resource === $resource && $this->action === $action;
    }

    public function getIdentifier(): string
    {
        return sprintf('%s:%s', $this->resource, $this->action);
    }
}
