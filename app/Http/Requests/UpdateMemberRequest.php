<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $memberId = $this->route('id') ?? $this->route('member');
        $templeId = $this->getTempleId();

        return [
            'temple_id' => 'nullable|integer|exists:temples,id',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:15|unique:members,phone,'.$memberId.',id,temple_id,'.$templeId,
            'email' => 'nullable|email|max:100',
            'role_id' => 'nullable|exists:roles,id',
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
            'name.required' => 'Member name is required.',
            'phone.required' => 'Phone number is required.',
            'phone.unique' => 'This phone number is already registered for this temple.',
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
