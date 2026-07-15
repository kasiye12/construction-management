<?php
namespace App\Services;

use App\Repositories\ProjectRepository;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProjectService
{
    protected ProjectRepository $projectRepository;

    public function __construct(ProjectRepository $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function getAllProjects(array $filters = [])
    {
        return $this->projectRepository->getFiltered($filters);
    }

    public function getProjectById(int $id)
    {
        return $this->projectRepository->findById($id, ['*'], [
            'costCategories', 'boqItems.laborResources', 
            'boqItems.materialResources', 'boqItems.equipmentResources',
            'subcontractors', 'ipcs'
        ]);
    }

    public function createProject(array $data)
    {
        return DB::transaction(function () use ($data) {
            $project = $this->projectRepository->create($data);
            
            $this->logActivity('created', 'Project', $project->id, [
                'name' => $project->name,
                'data' => $data
            ]);
            
            return $project;
        });
    }

    public function updateProject(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $oldData = $this->projectRepository->findById($id)->toArray();
            $this->projectRepository->update($id, $data);
            
            $this->logActivity('updated', 'Project', $id, [
                'old' => $oldData,
                'new' => $data
            ]);
            
            return true;
        });
    }

    public function deleteProject(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $project = $this->projectRepository->findById($id);
            $projectName = $project->name;
            
            $this->projectRepository->delete($id);
            
            $this->logActivity('deleted', 'Project', $id, [
                'name' => $projectName
            ]);
            
            return true;
        });
    }

    public function getDashboardStats(): array
    {
        return [
            'total_projects' => $this->projectRepository->count(),
            'active_projects' => $this->projectRepository->countByStatus('active'),
            'total_contract_value' => $this->projectRepository->sumContractValue(),
            'recent_projects' => $this->projectRepository->getRecent(5),
        ];
    }

    protected function logActivity(string $action, string $module, int $modelId, array $data = []): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'module' => $module,
            'description' => "{$action} {$module} #{$modelId}",
            'new_values' => json_encode($data),
            'ip_address' => request()->ip(),
        ]);
    }
}
