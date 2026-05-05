<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Delegation;
use App\Models\Partner;
use App\Models\File;
use App\Models\Task;
use Illuminate\Http\Request;

/**
 * Class GlobalSearchService
 *
 * Handles cross-resource searching for the application.
 *
 * @package App\Services
 */
class GlobalSearchService
{
    /**
     * Search across multiple resources.
     *
     * @param string $query
     * @return array
     */
    public function search(string $query): array
    {
        $limit = 5;

        $delegations = Delegation::where('name', 'like', "%{$query}%")
            ->orWhere('code', 'like', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'name', 'code'])
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->name,
                'subtitle' => $item->code,
                'type' => 'delegation',
                'url' => "/delegations/{$item->id}"
            ]);

        $partners = Partner::where('partner_name', 'like', "%{$query}%")
            ->orWhere('partner_code', 'like', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'partner_name', 'partner_code'])
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->partner_name,
                'subtitle' => $item->partner_code,
                'type' => 'partner',
                'url' => "/partners/{$item->id}"
            ]);

        $files = File::where('file_name', 'like', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'file_name', 'mime_type'])
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->file_name,
                'subtitle' => $item->mime_type,
                'type' => 'file',
                'url' => "/documents" // Files don't have individual detail pages yet, link to document center
            ]);

        $tasks = Task::where('title', 'like', "%{$query}%")
            ->limit($limit)
            ->get(['id', 'title', 'status'])
            ->map(fn($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'subtitle' => "Status: {$item->status}",
                'type' => 'task',
                'url' => "/tasks"
            ]);

        return [
            'delegations' => $delegations,
            'partners' => $partners,
            'files' => $files,
            'tasks' => $tasks,
            'query' => $query,
            'total' => count($delegations) + count($partners) + count($files) + count($tasks)
        ];
    }
}
