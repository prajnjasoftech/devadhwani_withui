<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $templeId = $this->getTempleId();

        return [
            'temple_id' => 'nullable|integer|exists:temples,id',
            'role_name' => 'required|string|max:100|unique:roles,role_name,NULL,id,temple_id,'.$templeId,
            'role' => 'nullable|array',
        ];
    }

    protected function getTempleId(): ?int
    {
        if (auth()->check() && auth()->user() instanceof \App\Models\Temple) {
            return auth()->user()->id;
        }

        return $this->input('temple_id');
    }

    public function messages(): array
    {
        return [
            'role_name.required' => 'Role name is required.',
            'role_name.unique' => 'This role name already exists for this temple.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'status' => false,
                'error' => collect($validator->errors()->all())->implode(', '),
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
