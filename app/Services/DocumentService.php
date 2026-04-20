<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DocumentRepository\DocumentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

final class DocumentService
{
    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
    ) {
    }

    public function getFolders(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->documentRepository->getFolders($request),
                'message' => __('documents.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('DocumentService::getFolders', ['error' => $throwable->getMessage()]);

            return ['success' => false, 'data' => null, 'message' => __('documents.messages.fetch_error')];
        }
    }

    public function createFolder(array $attributes): ?array
    {
        try {
            return $this->documentRepository->createFolder($attributes);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::createFolder', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function getFiles(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->documentRepository->getFiles($request),
                'message' => __('documents.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('DocumentService::getFiles', ['error' => $throwable->getMessage()]);

            return ['success' => false, 'data' => null, 'message' => __('documents.messages.fetch_error')];
        }
    }

    public function getFileById(string $id): ?array
    {
        try {
            return $this->documentRepository->getFileById($id);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::getFileById', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function uploadFile(array $attributes, ?UploadedFile $file = null): ?array
    {
        try {
            return $this->documentRepository->uploadFile($attributes, $file);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::uploadFile', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function updateFile(string $id, array $attributes): ?array
    {
        try {
            return $this->documentRepository->updateFile($id, $attributes);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::updateFile', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function createDownloadUrl(string $id): ?array
    {
        try {
            return $this->documentRepository->createDownloadUrl($id);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::createDownloadUrl', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }

    public function shareFile(string $id, array $attributes): ?array
    {
        try {
            return $this->documentRepository->shareFile($id, $attributes);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::shareFile', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }
}
