<?php

declare(strict_types=1);

namespace App\Repositories\TaskRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface TaskRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;
}
