<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DocumentRepository\DocumentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Class DocumentService
 *
 * Orchestrates business logic for document and folder management, including uploads, sharing, and download URL generation.
 *
 * @package App\Services
 */
final class DocumentService
{
    /**
     * DocumentService constructor.
     *
     * @param DocumentRepositoryInterface $documentRepository
     */
    public function __construct(
        private DocumentRepositoryInterface $documentRepository,
    ) {
    }

    /**
     * Retrieve a categorized list of document folders.
     *
     * @param Request $request
     * @return array Response structure with success status, folder data, and message.
     */
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

    /**
     * Create a new document folder.
     *
     * @param array $attributes Folder properties (name, parent_id, etc.).
     * @return array|null Created folder data or null on failure.
     */
    public function createFolder(array $attributes): ?array
    {
        try {
            return $this->documentRepository->createFolder($attributes);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::createFolder', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    /**
     * Retrieve a list of files based on folder or search filters.
     *
     * @param Request $request
     * @return array Response structure with success status, file data, and message.
     */
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

    /**
     * Retrieve details for a specific file.
     *
     * @param string $id
     * @return array|null File data or null on failure.
     */
    public function getFileById(string $id): ?array
    {
        try {
            return $this->documentRepository->getFileById($id);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::getFileById', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }

    /**
     * Handle file upload and metadata persistence.
     *
     * @param array $attributes Metadata for the document.
     * @param UploadedFile|null $file The uploaded file binary.
     * @return array|null Uploaded file data or null on failure.
     */
    public function uploadFile(array $attributes, ?UploadedFile $file = null): ?array
    {
        try {
            return $this->documentRepository->uploadFile($attributes, $file);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::uploadFile', ['error' => $throwable->getMessage()]);

            return null;
        }
    }

    /**
     * Update an existing file's metadata.
     *
     * @param string $id
     * @param array $attributes New metadata to apply.
     * @return array|null Updated file data or null on failure.
     */
    public function updateFile(string $id, array $attributes): ?array
    {
        try {
            return $this->documentRepository->updateFile($id, $attributes);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::updateFile', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }

    /**
     * Generate a temporary secure download URL for a file.
     *
     * @param string $id
     * @return array|null Response with the download URL or null on failure.
     */
    public function createDownloadUrl(string $id): ?array
    {
        try {
            return $this->documentRepository->createDownloadUrl($id);
        } catch (Throwable $throwable) {
            Log::error('DocumentService::createDownloadUrl', ['id' => $id, 'error' => $throwable->getMessage()]);

            return null;
        }
    }

    /**
     * Share a file with specific users or via a public link (depending on attributes).
     *
     * @param string $id
     * @param array $attributes Sharing settings (permissions, expires_at, etc.).
     * @return array|null Sharing record data or null on failure.
     */
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
