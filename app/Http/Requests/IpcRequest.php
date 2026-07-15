<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IpcRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'manager', 'engineer']);
    }

    public function rules()
    {
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'subcontractor_id' => 'required|exists:subcontractors,id',
            'ipc_number' => 'required|string|max:50',
            'issue_number' => 'required|integer|min:1',
            'ipc_date' => 'required|date',
            'period_start_date' => 'required|date',
            'period_end_date' => 'required|date|after:period_start_date',
            'retention_percentage' => 'nullable|numeric|min:0|max:100',
            'remarks' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,submitted,approved,paid',
        ];

        // Validate items if present
        if ($this->has('items')) {
            $rules['items'] = 'array';
            $rules['items.*.boq_item_id'] = 'required|exists:boq_items,id';
            $rules['items.*.contract_quantity'] = 'required|numeric|min:0';
            $rules['items.*.contract_amount'] = 'required|numeric|min:0';
            $rules['items.*.current_quantity'] = 'required|numeric|min:0';
            $rules['items.*.current_quantity'] = [
                'required', 'numeric', 'min:0',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1];
                    $boqItemId = $this->input("items.{$index}.boq_item_id");
                    $boqItem = \App\Models\BoqItem::find($boqItemId);
                    
                    if ($boqItem && $value > $boqItem->quantity) {
                        $fail("Current quantity ({$value}) cannot exceed contract quantity ({$boqItem->quantity}).");
                    }
                },
            ];
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'project_id.required' => 'Please select a project.',
            'subcontractor_id.required' => 'Please select a subcontractor.',
            'ipc_date.required' => 'IPC date is required.',
            'period_end_date.after' => 'Period end date must be after start date.',
            'items.required' => 'At least one item is required.',
        ];
    }
}
