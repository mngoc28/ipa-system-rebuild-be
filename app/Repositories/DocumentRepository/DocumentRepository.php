<?php

declare(strict_types=1);

namespace App\Repositories\DocumentRepository;

use App\Models\File;
use App\Models\FileShare;
use App\Models\FileVersion;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DocumentRepository implements DocumentRepositoryInterface
{
    public function getFolders(Request $request): array
    {
        $query = Folder::query();

        if ($request->filled('parentId')) {
            $query->where('parent_folder_id', (int) $request->input('parentId'));
        }

        if ($request->filled('scopeType')) {
            $query->where('scope_type', $this->resolveScopeType((string) $request->input('scopeType')));
        }

        $items = $query->orderBy('folder_name', 'asc')
            ->get()
            ->map(fn (Folder $folder): array => $this->normalizeFolder($folder))
            ->all();

        return ['items' => $items];
    }

    public function createFolder(array $attributes): array
    {
        $folder = Folder::create([
            'parent_folder_id' => Arr::get($attributes, 'parent_folder_id'),
            'folder_name' => (string) Arr::get($attributes, 'folder_name'),
            'owner_user_id' => (int) Arr::get($attributes, 'owner_user_id', 0),
            'scope_type' => $this->resolveScopeType((string) Arr::get($attributes, 'scope_type', 'GENERAL')),
        ]);

        return $this->normalizeFolder($folder);
    }

    public function getFiles(Request $request): array
    {
        $query = File::query();

        if ($request->filled('folderId')) {
            $query->where('folder_id', (int) $request->input('folderId'));
        }

        $items = $query->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (File $file): array => $this->normalizeFile($file))
            ->all();

        return ['items' => $items];
    }

    public function getFileById(string $id): ?array
    {
        $file = File::query()->where('id', $id)->first();

        if (! $file) {
            return null;
        }

        return [
            'file' => $this->normalizeFile($file),
            'versions' => FileVersion::query()
                ->where('file_id', $file->id)
                ->orderByDesc('version_no')
                ->get()
                ->map(fn (FileVersion $version): array => $this->normalizeVersion($version))
                ->all(),
            'shares' => FileShare::query()
                ->where('file_id', $file->id)
                ->orderByDesc('id')
                ->get()
                ->map(fn (FileShare $share): array => $this->normalizeShare($share))
                ->all(),
        ];
    }

    public function uploadFile(array $attributes, ?UploadedFile $file = null): array
    {
        return DB::transaction(function () use ($attributes, $file): array {
            $fileName = (string) Arr::get($attributes, 'file_name');
            $sizeBytes = (int) Arr::get($attributes, 'size_bytes', 0);
            $uploadedBy = (int) Arr::get($attributes, 'uploaded_by', 0);

            $record = File::create([
                'folder_id' => Arr::get($attributes, 'folder_id'),
                'file_name' => $fileName,
                'file_ext' => pathinfo($fileName, PATHINFO_EXTENSION) ?: null,
                'mime_type' => $file?->getMimeType(),
                'size_bytes' => $sizeBytes,
                'storage_key' => '',
                'checksum' => null,
                'uploaded_by' => $uploadedBy,
                'delegation_id' => Arr::get($attributes, 'delegation_id'),
                'minutes_id' => Arr::get($attributes, 'minutes_id'),
                'task_id' => Arr::get($attributes, 'task_id'),
            ]);

            $storageKey = $this->buildStorageKey($record->id, $fileName);

            if ($file instanceof UploadedFile) {
                Storage::disk('public')->put($storageKey, file_get_contents($file->getRealPath()) ?: '');
            } else {
                Storage::disk('public')->put($storageKey, sprintf("Document placeholder for %s\n", $fileName));
            }

            $record->storage_key = $storageKey;
            $record->save();

            FileVersion::create([
                'file_id' => $record->id,
                'version_no' => 1,
                'storage_key' => $storageKey,
                'size_bytes' => $sizeBytes,
                'updated_by' => $uploadedBy,
            ]);

            return $this->normalizeFile($record->refresh());
        });
    }

    public function updateFile(string $id, array $attributes): ?array
    {
        $record = File::query()->where('id', $id)->first();

        if (! $record) {
            return null;
        }

        $record->file_name = (string) Arr::get($attributes, 'file_name', $record->file_name);
        $record->file_ext = pathinfo($record->file_name, PATHINFO_EXTENSION) ?: $record->file_ext;
        $record->save();

        return $this->normalizeFile($record->refresh());
    }

    public function createDownloadUrl(string $id): ?array
    {
        $record = File::query()->where('id', $id)->first();

        if (! $record) {
            return null;
        }

        $expiresAt = now()->addDay();

        $baseUrl = rtrim((string) config('app.url'), '/');

        return [
            'url' => $baseUrl . '/storage/' . ltrim($record->storage_key, '/'),
            'expiresAt' => $expiresAt->toIso8601String(),
        ];
    }

    public function shareFile(string $id, array $attributes): ?array
    {
        $record = File::query()->where('id', $id)->first();

        if (! $record) {
            return null;
        }

        $sharedWithUserId = $this->resolveUserTargetId(Arr::get($attributes, 'shared_with_user_id'));
        $sharedWithRoleId = $this->resolveRoleTargetId(Arr::get($attributes, 'shared_with_role_id'));

        $share = FileShare::create([
            'file_id' => $record->id,
            'shared_with_user_id' => $sharedWithUserId,
            'shared_with_role_id' => $sharedWithRoleId,
            'permission_level' => $this->resolvePermissionLevel((string) Arr::get($attributes, 'permission_level', 'VIEW')),
            'expires_at' => Arr::get($attributes, 'expires_at'),
        ]);

        return ['shareId' => (string) $share->id];
    }

    private function normalizeFolder(Folder $folder): array
    {
        return [
            'id' => (string) $folder->id,
            'parentFolderId' => $folder->parent_folder_id ? (string) $folder->parent_folder_id : null,
            'folderName' => $folder->folder_name,
            'scopeType' => $this->formatScopeType((int) $folder->scope_type),
            'ownerUserId' => (string) $folder->owner_user_id,
            'createdAt' => optional($folder->created_at)?->toIso8601String(),
            'updatedAt' => optional($folder->updated_at)?->toIso8601String(),
        ];
    }

    private function normalizeFile(File $file): array
    {
        return [
            'id' => (string) $file->id,
            'folderId' => $file->folder_id ? (string) $file->folder_id : null,
            'fileName' => $file->file_name,
            'fileExt' => $file->file_ext,
            'mimeType' => $file->mime_type,
            'sizeBytes' => (int) $file->size_bytes,
            'storageKey' => $file->storage_key,
            'checksum' => $file->checksum,
            'uploadedBy' => (string) $file->uploaded_by,
            'delegationId' => $file->delegation_id ? (string) $file->delegation_id : null,
            'minutesId' => $file->minutes_id ? (string) $file->minutes_id : null,
            'taskId' => $file->task_id ? (string) $file->task_id : null,
            'createdAt' => optional($file->created_at)?->toIso8601String(),
            'updatedAt' => optional($file->updated_at)?->toIso8601String(),
        ];
    }

    private function normalizeVersion(FileVersion $version): array
    {
        return [
            'id' => (string) $version->id,
            'fileId' => (string) $version->file_id,
            'versionNo' => (int) $version->version_no,
            'storageKey' => $version->storage_key,
            'sizeBytes' => (int) $version->size_bytes,
            'updatedBy' => (string) $version->updated_by,
            'createdAt' => optional($version->created_at)?->toIso8601String(),
            'updatedAt' => optional($version->updated_at)?->toIso8601String(),
        ];
    }

    private function normalizeShare(FileShare $share): array
    {
        return [
            'id' => (string) $share->id,
            'fileId' => (string) $share->file_id,
            'sharedWithUserId' => $share->shared_with_user_id ? (string) $share->shared_with_user_id : null,
            'sharedWithRoleId' => $share->shared_with_role_id ? (string) $share->shared_with_role_id : null,
            'permissionLevel' => $this->formatPermissionLevel((int) $share->permission_level),
            'expiresAt' => optional($share->expires_at)?->toIso8601String(),
            'createdAt' => optional($share->created_at)?->toIso8601String(),
            'updatedAt' => optional($share->updated_at)?->toIso8601String(),
        ];
    }

    private function buildStorageKey(int|string $fileId, string $fileName): string
    {
        $safeName = Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) ?: 'document';
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $suffix = $extension !== '' ? '.' . $extension : '';

        return sprintf('documents/%s/%s%s', $fileId, $safeName . '-' . Str::uuid(), $suffix);
    }

    private function resolveScopeType(string|int|null $scopeType): int
    {
        if (is_int($scopeType) || ctype_digit((string) $scopeType)) {
            return (int) $scopeType;
        }

        return match (strtoupper(trim((string) $scopeType))) {
            'DELEGATION' => 1,
            'MINUTES' => 2,
            'TASK' => 3,
            'EVENT' => 4,
            'PARTNER' => 5,
            default => 0,
        };
    }

    private function formatScopeType(int $scopeType): string
    {
        return match ($scopeType) {
            1 => 'DELEGATION',
            2 => 'MINUTES',
            3 => 'TASK',
            4 => 'EVENT',
            5 => 'PARTNER',
            default => 'GENERAL',
        };
    }

    private function resolvePermissionLevel(string $permissionLevel): int
    {
        return strtoupper(trim($permissionLevel)) === 'EDIT' ? 1 : 0;
    }

    private function resolveUserTargetId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || ctype_digit((string) $value)) {
            return (int) $value;
        }

        $resolved = DB::table('ipa_user')
            ->where('username', (string) $value)
            ->orWhere('email', (string) $value)
            ->value('id');

        return $resolved !== null ? (int) $resolved : (int) (DB::table('ipa_user')->min('id') ?? 0);
    }

    private function resolveRoleTargetId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value) || ctype_digit((string) $value)) {
            return (int) $value;
        }

        $resolved = DB::table('ipa_role')
            ->where('code', (string) $value)
            ->orWhere('name', (string) $value)
            ->value('id');

        return $resolved !== null ? (int) $resolved : (int) (DB::table('ipa_role')->min('id') ?? 0);
    }

    private function formatPermissionLevel(int $permissionLevel): string
    {
        return $permissionLevel === 1 ? 'EDIT' : 'VIEW';
    }
}
