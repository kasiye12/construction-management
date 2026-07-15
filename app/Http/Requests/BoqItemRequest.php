<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BoqItemRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'engineer']);
    }

    public function rules()
    {
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'cost_category_id' => 'nullable|exists:cost_categories,id',
            'parent_id' => 'nullable|exists:boq_items,id',
            'item_number' => 'required|string|max:50',
            'description' => 'required|string|max:1000',
            'unit' => 'required|string|max:50|in:m2,m3,kg,pcs,LS,m,liter,roll,cylinder,bag',
            'quantity' => 'required|numeric|min:0|max:99999999',
            'unit_rate' => 'required|numeric|min:0|max:99999999',
            'duration_days' => 'nullable|integer|min:0|max:3650',
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'status' => 'nullable|in:pending,in_progress,completed',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'Selected project does not exist.',
            'item_number.required' => 'Item number is required.',
            'description.required' => 'Item description is required.',
            'unit.required' => 'Unit of measurement is required.',
            'unit.in' => 'Invalid unit selected.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity cannot be negative.',
            'unit_rate.required' => 'Unit rate is required.',
            'unit_rate.min' => 'Unit rate cannot be negative.',
            'planned_end_date.after_or_equal' => 'End date must be after start date.',
        ];
    }
}
