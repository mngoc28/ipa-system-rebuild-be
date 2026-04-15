<?php

declare(strict_types=1);

namespace App\Repositories\PartnerRepository;

use App\Models\Partner;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class PartnerRepository extends BaseRepository implements PartnerRepositoryInterface
{
    public function getModel(): string
    {
        return Partner::class;
    }

    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 20)));
        $search = $request->input('search');
        $status = $request->input('status');
        $sectorId = $request->input('sectorId');
        $countryId = $request->input('countryId');

        $query = DB::table('ipa_partner as p')
            ->leftJoin('ipa_country as c', 'c.id', '=', 'p.country_id')
            ->leftJoin('ipa_md_sector as s', 's.id', '=', 'p.sector_id')
            ->select([
                'p.*',
                'c.name_vi as country_name_vi',
                'c.name_en as country_name_en',
                's.name_vi as sector_name_vi',
            ])
            ->whereNull('p.deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('p.partner_name', 'like', "%{$search}%")
                  ->orWhere('p.partner_code', 'like', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('p.status', (int) $status);
        }

        if ($sectorId) {
            $query->where('p.sector_id', (int) $sectorId);
        }

        if ($countryId) {
            $query->where('p.country_id', (int) $countryId);
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('p.partner_name')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $items = $rows->map(function (object $row): array {
            return [
                'id' => (string) $row->id,
                'partnerCode' => (string) $row->partner_code,
                'partnerName' => (string) $row->partner_name,
                'countryId' => (int) $row->country_id,
                'countryName' => (string) ($row->country_name_vi ?? $row->country_name_en ?? ''),
                'sectorId' => (int) $row->sector_id,
                'sectorName' => (string) ($row->sector_name_vi ?? $row->sector_name_en ?? ''),
                'status' => (int) $row->status,
                'score' => (float) $row->score,
                'website' => (string) $row->website,
                'createdAt' => (string) $row->created_at,
            ];
        })->all();

        return [
            'items' => $items,
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => (int) ceil($total / $pageSize),
            ],
        ];
    }
}
