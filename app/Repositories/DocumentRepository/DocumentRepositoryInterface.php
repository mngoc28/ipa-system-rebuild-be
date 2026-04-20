<?php

declare(strict_types=1);

namespace App\Repositories\DocumentRepository;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Interface DocumentRepositoryInterface
 *
 * Provides specialized data access methods for documents and folders (file management system).
 *
 * @package App\Repositories\DocumentRepository
 */
interface DocumentRepositoryInterface
{
    /**
     * Get a list of folders with optional filtering.
     *
     * @param Request $request
     * @return array
     */
    public function getFolders(Request $request): array;

    /**
     * Create a new folder.
     *
     * @param array $attributes
     * @return array
     */
    public function createFolder(array $attributes): array;

    /**
     * Get a list of files with optional filtering (folder_id, keyword, etc.).
     *
     * @param Request $request
     * @return array
     */
    public function getFiles(Request $request): array;

    /**
     * Find a file by its ID and return detailed information.
     *
     * @param string $id
     * @return array|null
     */
    public function getFileById(string $id): ?array;

    /**
     * Upload a new file and optional metadata.
     *
     * @param array $attributes
     * @param UploadedFile|null $file
     * @return array
     */
    public function uploadFile(array $attributes, ?UploadedFile $file = null): array;

    /**
     * Update metadata for an existing file.
     *
     * @param string $id
     * @param array $attributes
     * @return array|null
     */
    public function updateFile(string $id, array $attributes): ?array;

    /**
     * Generate a temporary download URL for a file.
     *
     * @param string $id
     * @return array|null
     */
    public function createDownloadUrl(string $id): ?array;

    /**
     * Share a file with specified access rights and recipients.
     *
     * @param string $id
     * @param array $attributes
     * @return array|null
     */
    public function shareFile(string $id, array $attributes): ?array;
}
