<?php

declare(strict_types=1);

namespace App\Repositories\AdminUserRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface AdminUserRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;

    public function findByUserId(string $userId): ?array;

    public function createUser(array $attributes): array;

    public function updateUser(string $userId, array $attributes): ?array;

    public function lockUser(string $userId, bool $locked): ?array;
}