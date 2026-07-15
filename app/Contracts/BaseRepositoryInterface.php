<?php
namespace App\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface BaseRepositoryInterface
{
    public function all(array $columns = ['*'], array $relations = []): Collection;
    public function findById(int $id, array $columns = ['*'], array $relations = []): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): \Illuminate\Pagination\LengthAwarePaginator;
}
