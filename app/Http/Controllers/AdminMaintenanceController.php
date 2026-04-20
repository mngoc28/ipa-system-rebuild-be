<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Class AdminMaintenanceController
 *
 * Handles administrative maintenance tasks such as clearing various system caches
 * (route, view, config, etc.) via Artisan commands.
 *
 * @package App\Http\Controllers
 */
final class AdminMaintenanceController extends Controller
{
    /**
     * Clear all application caches (config, route, view, and generic cache).
     *
     * @return JsonResponse
     */
    public function clearCache(): JsonResponse
    {
        try {
            $commands = [
                'cache:clear',
                'config:clear',
                'route:clear',
                'view:clear',
                'optimize:clear',
            ];

            $outputs = [];

            foreach ($commands as $command) {
                Artisan::call($command);
                $outputs[$command] = trim((string) Artisan::output());
            }

            return $this->successResponse([
                'commands' => $commands,
                'outputs' => $outputs,
            ], __('maintenance.cache_cleared'));
        } catch (Throwable $throwable) {
            return $this->errorResponse(__('maintenance.cache_clear_error'), 'CACHE_CLEAR_FAILED', HttpStatus::BAD_REQUEST);
        }
    }
}
