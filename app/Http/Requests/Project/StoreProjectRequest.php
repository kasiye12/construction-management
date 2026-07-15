<?php
namespace App\Http\Requests\Project;

use App\Enums\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canCreateProjects();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:3'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'contractor_name' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date', 'before_or_equal:end_date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'contract_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'description' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::in(ProjectStatus::toArray())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('validation.project_name_required'),
            'name.min' => __('validation.project_name_min'),
            'start_date.before_or_equal' => __('validation.start_date_invalid'),
            'end_date.after_or_equal' => __('validation.end_date_invalid'),
            'contract_amount.max' => __('validation.amount_too_large'),
            'status.in' => __('validation.invalid_status'),
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('attributes.project_name'),
            'client_name' => __('attributes.client_name'),
            'contractor_name' => __('attributes.contractor_name'),
        ];
    }
}
