<?php

declare(strict_types=1);

namespace App\Repositories\PartnerRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

/**
 * Interface PartnerRepositoryInterface
 *
 * Provides specialized data access for partner/organization management.
 *
 * @package App\Repositories\PartnerRepository
 */
interface PartnerRepositoryInterface extends RepositoryInterface
{
    /**
     * Get a paginated list of partners with filtering and search capabilities.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array;
}
