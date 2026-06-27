<?php

namespace App\Contracts;

interface RepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []);
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);
    public function find(string $id, array $columns = ['*'], array $relations = []);
    public function findOrFail(string $id, array $columns = ['*'], array $relations = []);
    public function create(array $data);
    public function update(string $id, array $data);
    public function delete(string $id);
    public function where(array $conditions, array $columns = ['*'], array $relations = []);
    public function whereFirst(array $conditions, array $columns = ['*'], array $relations = []);
}