<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Class BaseRepository
 *
 * Abstract base class for all repositories, providing a default implementation of RepositoryInterface.
 *
 * @package App\Repositories
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * @var Model The Eloquent model instance.
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->model = app($this->getModel());
    }

    /**
     * Get the model class name.
     *
     * @return string
     */
    abstract public function getModel(): string;

    /**
     * Get all records.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function all()
    {
        return $this->model->newQuery()->get();
    }

    /**
     * Find a record by ID.
     *
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id)
    {
        return $this->model->newQuery()->find($id);
    }

    /**
     * Find a record by ID and return specific columns.
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findOnlyColumn($id, $columns = ['*'])
    {
        return $this->model->newQuery()->select($columns)->find($id);
    }

    /**
     * Get the first record.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first()
    {
        return $this->model->newQuery()->first();
    }

    /**
     * Create a new record.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($attributes = [])
    {
        return $this->model->newQuery()->create($attributes);
    }

    /**
     * Insert multiple records.
     *
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes)
    {
        return $this->model->newQuery()->insert($attributes);
    }

    /**
     * Update a record by ID.
     *
     * @param mixed $id
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     * @throws ModelNotFoundException
     */
    public function update($id, $attributes = [])
    {
        $record = $this->find($id);

        if (! $record) {
            throw new ModelNotFoundException();
        }

        $record->fill($attributes)->save();

        return $record;
    }

    /**
     * Delete a record by ID.
     *
     * @param mixed $id
     * @return bool
     */
    public function delete($id)
    {
        $record = $this->find($id);

        if (! $record) {
            return false;
        }

        return (bool) $record->delete();
    }

    /**
     * Show a record by ID.
     *
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($id)
    {
        return $this->find($id);
    }

    /**
     * Get the current query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->model->newQuery();
    }

    /**
     * Clear the current query builder by re-instantiating the model.
     *
     * @return $this
     */
    public function clearQuery()
    {
        $this->model = app($this->getModel());

        return $this;
    }

    /**
     * Find records by filter.
     *
     * @param array $filter
     * @param bool $toArray
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function findBy(array $filter, bool $toArray = true)
    {
        $query = $this->model->newQuery()->where($filter);

        return $toArray ? $query->get()->toArray() : $query->get();
    }

    /**
     * Find one record by filter.
     *
     * @param array $filter
     * @param bool $toArray
     * @return \Illuminate\Database\Eloquent\Model|array|null
     */
    public function findOneBy(array $filter, bool $toArray = true)
    {
        $query = $this->model->newQuery()->where($filter);
        $record = $query->first();

        return $toArray && $record ? $record->toArray() : $record;
    }

    /**
     * Paginate records.
     *
     * @param int|LengthAwarePaginator $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($page)
    {
        if ($page instanceof LengthAwarePaginator) {
            return $page;
        }

        return $this->model->newQuery()->paginate();
    }

    /**
     * Update records where conditions match.
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateWhere(array $attributes = [], array $params = []): void
    {
        $this->model->newQuery()->where($params)->update($attributes);
    }

    /**
     * Update or create a record.
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateOrCreate(array $attributes = [], array $params = []): void
    {
        $this->model->newQuery()->updateOrCreate($params, $attributes);
    }

    /**
     * Delete records by filter.
     *
     * @param array $filter
     * @return void
     */
    public function deleteBy(array $filter): void
    {
        $this->model->newQuery()->where($filter)->delete();
    }

    /**
     * Find records where column values match an array of values.
     *
     * @param array $filter Format: ['column_name' => [values]]
     * @param bool $toArray
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function findWhereIn(array $filter, bool $toArray = true)
    {
        $query = $this->model->newQuery();

        foreach ($filter as $column => $values) {
            $query->whereIn($column, $values);
        }

        return $toArray ? $query->get()->toArray() : $query->get();
    }

    /**
     * Delete records where column values match an array of values.
     *
     * @param array $filter Format: ['column_name' => [values]]
     * @return void
     */
    public function deleteWhereIn(array $filter): void
    {
        foreach ($filter as $column => $values) {
            $this->model->newQuery()->whereIn($column, $values)->delete();
        }
    }

    /**
     * Count records based on filter.
     *
     * @param array $filter
     * @return int
     */
    public function countRecord(array $filter = []): int
    {
        return (int) $this->model->newQuery()->where($filter)->count();
    }

    /**
     * Find records by an array of IDs.
     *
     * @param array $ids
     * @param array $filter
     * @param bool $returnOnlyIds
     * @return array
     */
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array
    {
        $query = $this->model->newQuery()->whereIn('id', $ids)->where($filter);

        if ($returnOnlyIds) {
            return $query->pluck('id')->toArray();
        }

        return $query->get()->toArray();
    }

    /**
     * Update records where a column value is in an array.
     *
     * @param string $column
     * @param array $values
     * @param array $attributes
     * @param array $whereConditions
     * @return void
     */
    public function updateWhereIn(string $column, array $values, array $attributes, array $whereConditions = []): void
    {
        $this->model->newQuery()->whereIn($column, $values)->where($whereConditions)->update($attributes);
    }

    /**
     * Update records where a column value is NOT in an array.
     *
     * @param string $column
     * @param array $values
     * @param array $attributes
     * @param array $whereConditions
     * @return void
     */
    public function updateWhereNotIn(string $column, array $values, array $attributes, array $whereConditions = []): void
    {
        $this->model->newQuery()->whereNotIn($column, $values)->where($whereConditions)->update($attributes);
    }

    /**
     * Delete records that are NOT in a set of IDs for a specific parent record.
     *
     * @param string $columnName
     * @param int $value
     * @param array $ids
     * @param string $primaryKey
     * @return void
     */
    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void
    {
        $this->model->newQuery()
            ->where($columnName, $value)
            ->whereNotIn($primaryKey, $ids)
            ->delete();
    }
}
