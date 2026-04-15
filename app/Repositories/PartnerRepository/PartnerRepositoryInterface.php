<?php

declare(strict_types=1);

namespace App\Repositories\PartnerRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;

interface PartnerRepositoryInterface extends RepositoryInterface
{
    public function getPaginated(Request $request): array;
}
