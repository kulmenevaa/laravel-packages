<?php declare(strict_types=1);

namespace Local\LaravelApiKit\Tests\Fixtures;

use Illuminate\Contracts\Auth\Authenticatable;

final class TestUser implements Authenticatable
{
    public function __construct(
        private readonly int|string $id = 1,
    ) {}

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): int|string
    {
        return $this->id;
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getAuthPassword(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // no-op
    }

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }
}
