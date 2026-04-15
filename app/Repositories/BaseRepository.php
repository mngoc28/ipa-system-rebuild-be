<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct()
    {
        $this->model = app($this->getModel());
    }

    abstract public function getModel(): string;

    public function all()
    {
        return $this->model->newQuery()->get();
    }

    public function find($id)
    {
        return $this->model->newQuery()->find($id);
    }

    public function findOnlyColumn($id, $columns = ['*'])
    {
        return $this->model->newQuery()->select($columns)->find($id);
    }

    public function first()
    {
        return $this->model->newQuery()->first();
    }

    public function create($attributes = [])
    {
        return $this->model->newQuery()->create($attributes);
    }

    public function insert(array $attributes)
    {
        return $this->model->newQuery()->insert($attributes);
    }

    public function update($id, $attributes = [])
    {
        $record = $this->find($id);

        if (! $record) {
            throw new ModelNotFoundException();
        }

        $record->fill($attributes)->save();

        return $record;
    }

    public function delete($id)
    {
        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }

    public function show($id)
    {
        return $this->find($id);
    }

    public function getQuery()
    {
        return $this->model->newQuery();
    }

    public function clearQuery()
    {
        $this->model = app($this->getModel());

        return $this;
    }

    public function findBy(array $filter, bool $toArray = true)
    {
        $query = $this->model->newQuery()->where($filter);

        return $toArray ? $query->get()->toArray() : $query->get();
    }

    public function findOneBy(array $filter, bool $toArray = true)
    {
        $query = $this->model->newQuery()->where($filter);
        $record = $query->first();

        return $toArray && $record ? $record->toArray() : $record;
    }

    public function paginate($page)
    {
        if ($page instanceof LengthAwarePaginator) {
            return $page;
        }

        return $this->model->newQuery()->paginate();
    }

    public function updateWhere(array $attributes = [], array $params = []): void
    {
        $this->model->newQuery()->where($params)->update($attributes);
    }

    public function updateOrCreate(array $attributes = [], array $params = []): void
    {
        $this->model->newQuery()->updateOrCreate($params, $attributes);
    }

    public function deleteBy(array $filter): void
    {
        $this->model->newQuery()->where($filter)->delete();
    }

    public function findWhereIn(array $filter, bool $toArray = true)
    {
        $query = $this->model->newQuery();

        foreach ($filter as $column => $values) {
            $query->whereIn($column, $values);
        }

        return $toArray ? $query->get()->toArray() : $query->get();
    }

    public function deleteWhereIn(array $filter): void
    {
        foreach ($filter as $column => $values) {
            $this->model->newQuery()->whereIn($column, $values)->delete();
        }
    }

    public function countRecord(array $filter = []): int
    {
        return (int) $this->model->newQuery()->where($filter)->count();
    }

    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array
    {
        $query = $this->model->newQuery()->whereIn('id', $ids)->where($filter);

        if ($returnOnlyIds) {
            return $query->pluck('id')->toArray();
        }

        return $query->get()->toArray();
    }

    public function updateWhereIn(string $column, array $values, array $attributes, array $whereConditions = []): void
    {
        $this->model->newQuery()->whereIn($column, $values)->where($whereConditions)->update($attributes);
    }

    public function updateWhereNotIn(string $column, array $values, array $attributes, array $whereConditions = []): void
    {
        $this->model->newQuery()->whereNotIn($column, $values)->where($whereConditions)->update($attributes);
    }

    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void
    {
        $this->model->newQuery()
            ->where($columnName, $value)
            ->whereNotIn($primaryKey, $ids)
            ->delete();
    }
}
