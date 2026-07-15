<?php
namespace App\Repositories;

use App\Models\Project;
use Illuminate\Support\Collection;

class ProjectRepository extends BaseRepository
{
    public function __construct(Project $project)
    {
        parent::__construct($project);
    }

    public function getFiltered(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('client_name', 'LIKE', "%{$search}%")
                  ->orWhere('contractor_name', 'LIKE', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')
                     ->paginate($filters['per_page'] ?? 15);
    }

    public function getRecent(int $limit = 5): Collection
    {
        return $this->model->latest()->take($limit)->get();
    }

    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }

    public function sumContractValue(): float
    {
        return (float) $this->model->sum('contract_amount');
    }

    public function count(): int
    {
        return $this->model->count();
    }
}
