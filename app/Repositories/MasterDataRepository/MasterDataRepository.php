<?php

declare(strict_types=1);

namespace App\Repositories\MasterDataRepository;

use App\Models\MasterData;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class MasterDataRepository extends BaseRepository implements MasterDataRepositoryInterface
{
    public function getModel(): string
    {
        return MasterData::class;
    }

    public function getAllowedDomains(): array
    {
        return array_keys(config('master_data.domains', []));
    }

    public function getAllOrSearch(string $domain, Request $request): array
    {
        $query = $this->model->newQuery()->where('domain', $domain);

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $query->where(function ($builder) use ($keyword): void {
                $builder->where('code', 'like', '%' . $keyword . '%')
                    ->orWhere('name_vi', 'like', '%' . $keyword . '%')
                    ->orWhere('name_en', 'like', '%' . $keyword . '%');
            });
        }

        $sortField = (string) $request->input('sort_field', 'sort_order');
        $sortDirection = strtolower((string) $request->input('sort_direction', 'asc'));
        $allowedSortFields = ['code', 'name_vi', 'name_en', 'sort_order', 'created_at', 'updated_at'];

        if (! in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'sort_order';
        }

        if (! in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'asc';
        }

        $items = $query->orderBy($sortField, $sortDirection)
            ->orderBy('name_vi', 'asc')
            ->get()
            ->map(static function (MasterData $item): array {
                return [
                    'id' => $item->id,
                    'code' => $item->code,
                    'name_vi' => $item->name_vi,
                    'name_en' => $item->name_en,
                    'sort_order' => $item->sort_order,
                    'is_active' => $item->is_active,
                    'created_at' => optional($item->created_at)?->toIso8601String(),
                    'updated_at' => optional($item->updated_at)?->toIso8601String(),
                ];
            })
            ->all();

        return [
            'items' => $items,
        ];
    }

    public function findByDomainAndId(string $domain, string $id): ?array
    {
        $item = $this->model->newQuery()
            ->where('domain', $domain)
            ->where('id', $id)
            ->first();

        return $item ? $this->normalize($item) : null;
    }

    public function createItem(string $domain, array $attributes): array
    {
        $payload = [
            'id' => (string) Str::uuid(),
            'domain' => $domain,
            'code' => Arr::get($attributes, 'code'),
            'name_vi' => Arr::get($attributes, 'name_vi'),
            'name_en' => Arr::get($attributes, 'name_en'),
            'sort_order' => (int) Arr::get($attributes, 'sort_order', 0),
            'is_active' => (bool) Arr::get($attributes, 'is_active', true),
            'created_by' => Arr::get($attributes, 'created_by'),
            'updated_by' => Arr::get($attributes, 'updated_by'),
        ];

        $record = $this->model->newQuery()->create($payload);

        return $this->normalize($record);
    }

    public function updateItem(string $domain, string $id, array $attributes): ?array
    {
        $record = $this->model->newQuery()
            ->where('domain', $domain)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return null;
        }

        $record->fill(array_filter([
            'code' => Arr::get($attributes, 'code'),
            'name_vi' => Arr::get($attributes, 'name_vi'),
            'name_en' => Arr::get($attributes, 'name_en'),
            'sort_order' => Arr::has($attributes, 'sort_order') ? (int) Arr::get($attributes, 'sort_order') : null,
            'is_active' => Arr::has($attributes, 'is_active') ? (bool) Arr::get($attributes, 'is_active') : null,
            'updated_by' => Arr::get($attributes, 'updated_by'),
        ], static fn ($value): bool => $value !== null));
        $record->save();

        return $this->normalize($record->refresh());
    }

    public function deleteItem(string $domain, string $id): bool
    {
        $record = $this->model->newQuery()
            ->where('domain', $domain)
            ->where('id', $id)
            ->first();

        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }

    private function normalize(MasterData $item): array
    {
        return [
            'id' => $item->id,
            'code' => $item->code,
            'name_vi' => $item->name_vi,
            'name_en' => $item->name_en,
            'sort_order' => $item->sort_order,
            'is_active' => $item->is_active,
            'created_at' => optional($item->created_at)?->toIso8601String(),
            'updated_at' => optional($item->updated_at)?->toIso8601String(),
        ];
    }
}
