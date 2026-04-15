<?php

namespace App\Services;

use App\Repositories\DelegationRepository\DelegationRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DelegationService
{
    protected $repository;

    public function __construct(DelegationRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function listDelegations(Request $request)
    {
        return $this->repository->getPaginated($request);
    }

    public function getDelegation(int $id)
    {
        return $this->repository->getById($id);
    }

    public function createDelegation(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                return $this->repository->create($data);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create delegation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateDelegation(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                return $this->repository->update($id, $data);
            });
        } catch (\Exception $e) {
            Log::error('Failed to update delegation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function deleteDelegation(int $id)
    {
        try {
            return $this->repository->delete($id);
        } catch (\Exception $e) {
            Log::error('Failed to delete delegation: ' . $e->getMessage());
            throw $e;
        }
    }
}
