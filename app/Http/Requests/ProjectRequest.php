<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']);
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|min:3',
            'client_name' => 'nullable|string|max:255',
            'contractor_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_amount' => 'nullable|numeric|min:0|max:999999999999',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,completed,on_hold,cancelled',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Project name is required.',
            'name.min' => 'Project name must be at least 3 characters.',
            'end_date.after_or_equal' => 'End date must be after or equal to start date.',
            'contract_amount.min' => 'Contract amount cannot be negative.',
            'contract_amount.max' => 'Contract amount is too large.',
            'status.in' => 'Invalid project status selected.',
        ];
    }
}
