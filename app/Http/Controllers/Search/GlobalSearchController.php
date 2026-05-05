<?php

declare(strict_types=1);

namespace App\Http\Controllers\Search;

use App\Http\Controllers\Controller;
use App\Services\GlobalSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Class GlobalSearchController
 *
 * Handles global search requests.
 *
 * @package App\Http\Controllers\Search
 */
class GlobalSearchController extends Controller
{
    protected $service;

    /**
     * GlobalSearchController constructor.
     *
     * @param GlobalSearchService $service
     */
    public function __construct(GlobalSearchService $service)
    {
        $this->service = $service;
    }

    /**
     * Perform a global search across various resources.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');

        if (strlen($query) < 2) {
            return $this->successResponse([
                'delegations' => [],
                'partners' => [],
                'files' => [],
                'tasks' => [],
                'query' => $query,
                'total' => 0
            ], 'Vui lòng nhập ít nhất 2 ký tự để tìm kiếm.');
        }

        $results = $this->service->search($query);

        return $this->successResponse($results, 'Tìm kiếm thành công.');
    }
}
