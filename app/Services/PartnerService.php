<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Partner;
use App\Repositories\PartnerRepository\PartnerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PartnerService
{
    public function __construct(
        private PartnerRepositoryInterface $partnerRepository,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->partnerRepository->getPaginated($request),
                'message' => __('partners.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PartnerService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partners.messages.fetch_error'),
            ];
        }
    }

    public function getOptions(): array
    {
        try {
            $countries = DB::table('ipa_country')
                ->select(['id', 'name_vi', 'name_en'])
                ->orderBy('name_vi')
                ->get()
                ->map(fn (object $country): array => [
                    'id' => (string) $country->id,
                    'label' => $this->resolveCountryLabel((string) $country->name_vi, (string) ($country->name_en ?? '')),
                ])
                ->values()
                ->all();

            $sectors = DB::table('ipa_md_sector')
                ->select(['id', 'name_vi'])
                ->orderBy('name_vi')
                ->get()
                ->map(fn (object $sector): array => [
                    'id' => (string) $sector->id,
                    'label' => $this->resolveSectorLabel((string) ($sector->name_vi ?? ''), (string) $sector->id),
                ])
                ->values()
                ->all();

            return [
                'success' => true,
                'data' => [
                    'countries' => $countries,
                    'sectors' => $sectors,
                ],
                'message' => __('partners.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PartnerService::getOptions', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partners.messages.fetch_error'),
            ];
        }
    }

    public function getById(int $id): array
    {
        try {
            $partner = $this->partnerRepository->find($id);

            if (!$partner) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('partners.messages.not_found'),
                ];
            }

            $partner->load(['contacts', 'interactions']);

            $country = DB::table('ipa_country')
                ->where('id', $partner->country_id)
                ->select(['name_vi', 'name_en'])
                ->first();

            $sector = DB::table('ipa_md_sector')
                ->where('id', $partner->sector_id)
                ->select(['name_vi'])
                ->first();

            $contacts = $partner->contacts->map(static fn ($contact): array => [
                'id' => (string) $contact->id,
                'fullName' => (string) $contact->full_name,
                'title' => $contact->title !== null ? (string) $contact->title : null,
                'email' => $contact->email !== null ? (string) $contact->email : null,
                'phone' => $contact->phone !== null ? (string) $contact->phone : null,
                'isPrimary' => (bool) $contact->is_primary,
            ])->all();

            $recentInteractions = $partner->interactions->take(5)->map(static fn ($interaction): array => [
                'id' => (string) $interaction->id,
                'interactionType' => (int) $interaction->interaction_type,
                'interactionAt' => optional($interaction->interaction_at)?->toDateTimeString(),
                'summary' => $interaction->summary !== null ? (string) $interaction->summary : null,
            ])->all();

            return [
                'success' => true,
                'data' => [
                    'id' => (string) $partner->id,
                    'partnerCode' => (string) $partner->partner_code,
                    'partnerName' => (string) $partner->partner_name,
                    'countryId' => (int) $partner->country_id,
                    'countryName' => $this->resolveCountryLabel((string) ($country->name_vi ?? ''), (string) ($country->name_en ?? '')),
                    'sectorId' => (int) $partner->sector_id,
                    'sectorName' => $this->resolveSectorLabel((string) ($sector->name_vi ?? ''), (string) $partner->sector_id),
                    'status' => (int) $partner->status,
                    'score' => $partner->score !== null ? (float) $partner->score : null,
                    'website' => $partner->website !== null ? (string) $partner->website : null,
                    'notes' => $partner->notes !== null ? (string) $partner->notes : null,
                    'createdAt' => $partner->created_at?->toDateTimeString(),
                    'contacts' => $contacts,
                    'recentInteractions' => $recentInteractions,
                ],
                'message' => __('partners.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PartnerService::getById', [
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partners.messages.fetch_error'),
            ];
        }
    }

    public function create(array $data): array
    {
        try {
            $partner = $this->partnerRepository->create($data);

            return [
                'success' => true,
                'data' => $partner,
                'message' => __('partners.messages.create_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PartnerService::create', [
                'data' => $data,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partners.messages.create_error'),
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $partner = $this->partnerRepository->update($id, $data);

            return [
                'success' => true,
                'data' => $partner,
                'message' => __('partners.messages.update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PartnerService::update', [
                'id' => $id,
                'data' => $data,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partners.messages.update_error'),
            ];
        }
    }

    public function delete(int $id): array
    {
        try {
            $deleted = $this->partnerRepository->delete($id);

            return [
                'success' => $deleted,
                'data' => null,
                'message' => $deleted ? __('partners.messages.delete_success') : __('partners.messages.not_found'),
            ];
        } catch (Throwable $throwable) {
            Log::error('PartnerService::delete', [
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('partners.messages.delete_error'),
            ];
        }
    }

    private function resolveCountryLabel(string $nameVi, string $nameEn): string
    {
        $label = trim($nameVi !== '' ? $nameVi : $nameEn);

        if ($label === '' || preg_match('/^name_vi_seed/i', $label) === 1 || preg_match('/^name_en_seed/i', $label) === 1) {
            return match (true) {
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'vietnam') => 'Việt Nam',
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'japan') => 'Nhật Bản',
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'korea') => 'Hàn Quốc',
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'singapore') => 'Singapore',
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'united states') => 'Hoa Kỳ',
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'germany') => 'Đức',
                str_contains(strtolower($nameVi . ' ' . $nameEn), 'taiwan') => 'Đài Loan',
                default => $label !== '' ? $label : 'Chưa xác định',
            };
        }

        return $label;
    }

    private function resolveSectorLabel(string $nameVi, string $sectorId): string
    {
        $label = trim($nameVi);

        if ($label === '' || preg_match('/^name_vi_seed/i', $label) === 1) {
            return match ($sectorId) {
                '1' => 'Công nghệ cao',
                '2' => 'Logistics',
                '3' => 'Fintech',
                '4' => 'Năng lượng tái tạo',
                default => $label !== '' ? $label : 'Chưa xác định',
            };
        }

        return $label;
    }
}
