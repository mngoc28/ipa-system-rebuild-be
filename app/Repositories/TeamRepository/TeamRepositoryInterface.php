<?php

declare(strict_types=1);

namespace App\Repositories\TeamRepository;

use Illuminate\Http\Request;

interface TeamRepositoryInterface
{
    public function getDashboard(Request $request): array;

    public function getUnits(Request $request): array;

    public function createMember(array $attributes): array;
}
