<?php

declare(strict_types=1);

namespace App\Repositories\ApprovalRepository;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface ApprovalRepositoryInterface
{
    public function getPaginated(Request $request): LengthAwarePaginator;

    public function getById(int $id): ?array;

    public function decide(int $id, array $data, int $userId): ?array;
}