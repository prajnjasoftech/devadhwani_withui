<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDevoteeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'temple_id' => 'required|integer|exists:temples,id',
            'devotee_name' => 'required|string|max:100',
            'devotee_phone' => 'nullable|string|max:20',
            'nakshatra' => 'nullable|string|max:100',
            'address' => 'nullable|string',
            'device_created_at' => 'nullable|date',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'error' => collect($validator->errors()->all())->implode(', '),
            'errors' => $validator->errors(),
        ], 422));
    }
}
