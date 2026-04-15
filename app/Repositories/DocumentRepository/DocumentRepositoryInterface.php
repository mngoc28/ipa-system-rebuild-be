<?php

declare(strict_types=1);

namespace App\Repositories\DocumentRepository;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

interface DocumentRepositoryInterface
{
    public function getFolders(Request $request): array;

    public function createFolder(array $attributes): array;

    public function getFiles(Request $request): array;

    public function getFileById(string $id): ?array;

    public function uploadFile(array $attributes, ?UploadedFile $file = null): array;

    public function updateFile(string $id, array $attributes): ?array;

    public function createDownloadUrl(string $id): ?array;

    public function shareFile(string $id, array $attributes): ?array;
}