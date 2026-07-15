<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostCategoryRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']);
    }

    public function rules()
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'code' => 'nullable|string|max:10|alpha_dash',
            'name' => 'required|string|max:255|min:2',
            'description' => 'nullable|string|max:500',
            'display_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages()
    {
        return [
            'project_id.required' => 'Please select a project.',
            'name.required' => 'Category name is required.',
            'code.alpha_dash' => 'Code can only contain letters, numbers, dashes and underscores.',
        ];
    }
}
