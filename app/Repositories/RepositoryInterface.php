<?php

declare(strict_types=1);

namespace App\Repositories;

interface RepositoryInterface
{
    public function all();

    public function find($id);

    public function findOnlyColumn($id, $columns = ['*']);

    public function first();

    public function create($attributes = []);

    public function insert(array $attributes);

    public function update($id, $attributes = []);

    public function delete($id);

    public function show($id);

    public function getQuery();

    public function clearQuery();

    public function findBy(array $filter, bool $toArray = true);

    public function findOneBy(array $filter, bool $toArray = true);

    public function paginate($page);

    public function updateWhere(array $attributes = [], array $params = []): void;

    public function updateOrCreate(array $attributes = [], array $params = []): void;

    public function deleteBy(array $filter): void;

    public function findWhereIn(array $filter, bool $toArray = true);

    public function deleteWhereIn(array $filter): void;

    public function countRecord(array $filter = []): int;

    public function findByIds(array $ids, array $filter = [], bool $returnOnlyIds = false): array;

    public function updateWhereIn(string $column, array $values, array $attributes, array $whereConditions = []): void;

    public function updateWhereNotIn(string $column, array $values, array $attributes, array $whereConditions = []): void;

    public function deleteNotInIds(string $columnName, int $value, array $ids, string $primaryKey = 'id'): void;
}
