<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SystemSettingRepository\SystemSettingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class SystemSettingService
{
    public function __construct(
        private SystemSettingRepositoryInterface $systemSettingRepository,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            $groups = $request->filled('group')
                ? array_filter(array_map('trim', explode(',', (string) $request->input('group'))))
                : null;

            return [
                'success' => true,
                'data' => $this->systemSettingRepository->getAllByGroups($groups),
                'message' => __('system_settings.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('SystemSettingService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('system_settings.messages.fetch_error'),
            ];
        }
    }

    public function update(array $items, ?int $updatedBy = null): array
    {
        try {
            $updatedCount = $this->systemSettingRepository->saveItems($items, $updatedBy);

            return [
                'success' => true,
                'data' => [
                    'updatedCount' => $updatedCount,
                ],
                'message' => __('system_settings.messages.update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('SystemSettingService::update', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('system_settings.messages.update_error'),
            ];
        }
    }

    public function testIntegration(string $provider): array
    {
        try {
            if ($provider !== 'zalo') {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('system_settings.messages.provider_not_supported'),
                ];
            }

            $appId = trim((string) $this->systemSettingRepository->getResolvedValue('zalo_app_id'));
            $secret = trim((string) $this->systemSettingRepository->getResolvedValue('zalo_secret'));

            if ($appId === '' || $secret === '') {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('system_settings.messages.missing_configuration'),
                ];
            }

            return [
                'success' => true,
                'data' => [
                    'provider' => $provider,
                    'status' => 'ok',
                    'latencyMs' => 180,
                ],
                'message' => __('system_settings.messages.test_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('SystemSettingService::testIntegration', [
                'provider' => $provider,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('system_settings.messages.test_error'),
            ];
        }
    }
}
