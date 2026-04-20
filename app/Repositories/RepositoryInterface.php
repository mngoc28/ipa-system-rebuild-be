<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Interface RepositoryInterface
 *
 * Base interface for all repositories, defining standard CRUD and query methods.
 *
 * @package App\Repositories
 */
interface RepositoryInterface
{
    /**
     * Get all records.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>
     */
    public function all();

    /**
     * Find a record by ID.
     *
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function find($id);

    /**
     * Find a record by ID and return specific columns.
     *
     * @param mixed $id
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function findOnlyColumn($id, $columns = ['*']);

    /**
     * Get the first record.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function first();

    /**
     * Create a new record.
     *
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create($attributes = []);

    /**
     * Insert multiple records.
     *
     * @param array $attributes
     * @return bool
     */
    public function insert(array $attributes);

    /**
     * Update a record by ID.
     *
     * @param mixed $id
     * @param array $attributes
     * @return bool|mixed
     */
    public function update($id, $attributes = []);

    /**
     * Delete a record by ID.
     *
     * @param mixed $id
     * @return bool|null
     */
    public function delete($id);

    /**
     * Show a record by ID.
     *
     * @param mixed $id
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function show($id);

    /**
     * Get the current query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery();

    /**
     * Clear the current query builder.
     *
     * @return void
     */
    public function clearQuery();

    /**
     * Find records by filter.
     *
     * @param array $filter
     * @param bool $toArray
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function findBy(array $filter, bool $toArray = true);

    /**
     * Find one record by filter.
     *
     * @param array $filter
     * @param bool $toArray
     * @return \Illuminate\Database\Eloquent\Model|array|null
     */
    public function findOneBy(array $filter, bool $toArray = true);

    /**
     * Paginate records.
     *
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($page);

    /**
     * Update records where conditions match.
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateWhere(array $attributes = [], array $params = []): void;

    /**
     * Update or create a record.
     *
     * @param array $attributes
     * @param array $params
     * @return void
     */
    public function updateOrCreate(array $attributes = [], array $params = []): void;

    /**
     * Delete records by filter.
     *
     * @param array $filter
     * @return void
     */
    public function deleteBy(array $filter): void;

    /**
     * Find records where a column value is in an array.
     *
     * @param array $filter Format: ['column_name' => [values]]
     * @param bool $toArray
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function findWhereIn(array $filter, bool $toArray = true);

    /**
     * Delete records where a column value is in an array.
     *
     * @param array $filter Format: ['column_name' => [values]]
     * @return void
     */
    public function deleteWhereIn(array $filter): void;

    /**
     * Count records based on filter.
     *
     * @param array $filter
     * @return int
     */
    public function countRecord(array $filter = []): int;

    /**
     * Find records by an array of IDs.
     *
     * @param array $ids
     * @param array $filter
     * @param bool $returnOnlyIds
     * @return array
     */
    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array;

    /**
     * Update records where a column value is in an array.
     *
     * @param string $column
     * @param array $values
     * @param array $attributes
     * @param array $whereConditions
     * @return void
     */
    public function updateWhereIn(string $column, array $values, array $attributes, array $whereConditions = []): void;

    /**
     * Update records where a column value is NOT in an array.
     *
     * @param string $column
     * @param array $values
     * @param array $attributes
     * @param array $whereConditions
     * @return void
     */
    public function updateWhereNotIn(string $column, array $values, array $attributes, array $whereConditions = []): void;

    /**
     * Delete records that are NOT in a set of IDs for a specific parent record.
     *
     * @param string $columnName The foreign key column.
     * @param int $value The parent record ID.
     * @param array $ids The list of IDs to keep.
     * @param string $primaryKey The primary key of the table (default 'id').
     * @return void
     */
    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void;
}
