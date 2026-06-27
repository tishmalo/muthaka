<?php

namespace App\Repositories;

use App\Contracts\RepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository implements RepositoryInterface
{
    protected Model $model;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function all(array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    public function find(string $id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->find($id, $columns);
    }

    public function findOrFail(string $id, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data)
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }

    public function delete(string $id): bool
    {
        $record = $this->findOrFail($id);
        return $record->delete();
    }

    public function where(array $conditions, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->where($conditions)->get($columns);
    }

    public function whereFirst(array $conditions, array $columns = ['*'], array $relations = [])
    {
        return $this->model->with($relations)->where($conditions)->first($columns);
    }

    protected function query(): Builder
    {
        return $this->model->newQuery();
    }
}