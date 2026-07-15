<?php
namespace App\Http\Requests\Project;

class UpdateProjectRequest extends StoreProjectRequest
{
    public function rules(): array
    {
        $rules = parent::rules();
        // Make name unique except for current project
        $rules['name'][] = 'unique:projects,name,' . $this->route('project');
        return $rules;
    }
}
