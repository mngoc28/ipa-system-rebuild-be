<?php

declare(strict_types=1);

namespace App\Repositories\MasterDataRepository;

use App\Models\MasterData;
use App\Models\Sector;
use App\Models\Location;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class MasterDataRepository extends BaseRepository implements MasterDataRepositoryInterface
{
    private function getTargetBuilder(string $domain): Builder
    {
        return match ($domain) {
            'sector' => (new Sector())->newQuery(),
            'location' => (new Location())->newQuery(),
            default => (new MasterData())->newQuery(),
        };
    }

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
        $query = $this->getTargetBuilder($domain);

        if ($domain !== 'sector' && $domain !== 'location') {
            $query->where('domain', $domain);
        }

        if ($request->filled('q')) {
            $keyword = trim((string) $request->input('q'));

            $query->where(function ($builder) use ($keyword, $domain): void {
                if ($domain === 'location') {
                    $builder->where('name', 'like', '%' . $keyword . '%');
                } else {
                    $builder->where('code', 'like', '%' . $keyword . '%')
                        ->orWhere('name_vi', 'like', '%' . $keyword . '%')
                        ->orWhere('name_en', 'like', '%' . $keyword . '%');
                }
            });
        }

        $defaultSort = match ($domain) {
            'location' => 'name',
            'sector' => 'id',
            default => 'sort_order'
        };
        $sortField = (string) $request->input('sort_field', $defaultSort);
        $sortDirection = strtolower((string) $request->input('sort_direction', 'asc'));

        $items = $query->orderBy($sortField, $sortDirection)
            ->get()
            ->map(fn ($item): array => $this->normalize($item, $domain))
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

    private function normalize($item, string $domain = ''): array
    {
        if ($domain === 'location') {
            return [
                'id' => $item->id,
                'code' => $item->id, // Location might not have code, use id as fallback
                'name_vi' => $item->name,
                'name_en' => $item->name,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => optional($item->created_at)?->toIso8601String(),
                'updated_at' => optional($item->updated_at)?->toIso8601String(),
            ];
        }

        return [
            'id' => $item->id,
            'code' => $item->code ?? '',
            'name_vi' => $item->name_vi ?? '',
            'name_en' => $item->name_en ?? '',
            'sort_order' => $item->sort_order ?? 0,
            'is_active' => $item->is_active ?? true,
            'created_at' => optional($item->created_at)?->toIso8601String(),
            'updated_at' => optional($item->updated_at)?->toIso8601String(),
        ];
    }
}
