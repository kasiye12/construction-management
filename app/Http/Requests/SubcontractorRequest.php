<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubcontractorRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'manager']);
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|min:2',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50|regex:/^[+]?[\d\s()-]{7,20}$/',
            'address' => 'nullable|string|max:500',
            'tax_id' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'Subcontractor name is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }
}
