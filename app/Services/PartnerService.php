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
        private readonly PartnerRepositoryInterface $partnerRepository,
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
                ->map(static fn (object $country): array => [
                    'id' => (string) $country->id,
                    'label' => (string) ($country->name_vi ?? $country->name_en ?? ''),
                ])
                ->values()
                ->all();

            $sectors = DB::table('ipa_md_sector')
                ->select(['id', 'name_vi'])
                ->orderBy('name_vi')
                ->get()
                ->map(static fn (object $sector): array => [
                    'id' => (string) $sector->id,
                    'label' => (string) ($sector->name_vi ?? ''),
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
                    'countryName' => (string) ($country->name_vi ?? $country->name_en ?? ''),
                    'sectorId' => (int) $partner->sector_id,
                    'sectorName' => (string) ($sector->name_vi ?? ''),
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
}
