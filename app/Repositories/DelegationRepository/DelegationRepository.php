<?php

namespace App\Repositories\DelegationRepository;

use App\Models\Delegation;
use Illuminate\Http\Request;

class DelegationRepository implements DelegationRepositoryInterface
{
    private $model;

    public function __construct(Delegation $model)
    {
        $this->model = $model;
    }

    public function getPaginated(Request $request)
    {
        $query = $this->model->newQuery();

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->has('direction')) {
            $query->where('direction', $request->get('direction'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        // Sorting
        $query->orderBy('created_at', 'desc');

        return $query->paginate($request->get('per_page', 10));
    }

    public function getById(int $id)
    {
        return $this->model->with(['members', 'events', 'outcomes'])->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $record->update($data);
            return $record;
        }
        return null;
    }

    public function delete(int $id)
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }
}
