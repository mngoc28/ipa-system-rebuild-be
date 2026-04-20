<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Enums\HttpStatus;

final class AnnouncementController extends Controller
{
    /**
     * Get list of announcements with pagination and basic filter.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::query();

        if ($request->has('search')) {
            $searchTerm = $request->query('search');
            $query->where('title', 'like', "%{$searchTerm}%");
        }

        $announcements = $query->orderBy('created_at', 'desc')->get();

        return $this->successResponse($announcements, 'Announcements fetched successfully.');
    }

    /**
     * Store a new announcement.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'type' => 'required|in:info,warning,success',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $announcement = Announcement::create($validated);

        return $this->successResponse($announcement, 'Announcement created successfully.', HttpStatus::CREATED);
    }

    /**
     * Update an announcement.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->errorResponse('Announcement not found.', 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'type' => 'sometimes|in:info,warning,success',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $announcement->update($validated);

        return $this->successResponse($announcement, 'Announcement updated successfully.');
    }

    /**
     * Remove an announcement.
     */
    public function destroy(int $id): JsonResponse
    {
        $announcement = Announcement::find($id);

        if (!$announcement) {
            return $this->errorResponse('Announcement not found.', 'NOT_FOUND', HttpStatus::NOT_FOUND);
        }

        $announcement->delete();

        return $this->successResponse(null, 'Announcement deleted successfully.');
    }
}
