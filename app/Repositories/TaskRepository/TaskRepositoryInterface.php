<?php

declare(strict_types=1);

namespace App\Repositories\TaskRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface TaskRepositoryInterface
 *
 * Provides specialized data access for system tasks, including filtered and paginated retrieval.
 *
 * @package App\Repositories\TaskRepository
 */
interface TaskRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of tasks filtered by request parameters.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;
}
