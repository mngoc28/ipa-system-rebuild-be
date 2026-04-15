<?php

namespace App\Repositories\DelegationRepository;

use Illuminate\Http\Request;

interface DelegationRepositoryInterface
{
    public function getPaginated(Request $request);
    public function getById(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id);
}
