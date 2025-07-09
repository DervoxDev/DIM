<?php

namespace App\Http\Requests\Auth;

use App\Helpers\CountryHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'country_id' => [
                'required',
                'string',
                'size:3',
                function ($attribute, $value, $fail) {
                    if (!CountryHelper::isValidCountryCode($value)) {
                        $fail(__('validation.The selected country is invalid.'));
                    }
                },
            ],
            'terms' => ['required', 'accepted'],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.The name field is required.'),
            'email.required' => __('validation.The email field is required.'),
            'email.unique' => __('validation.The email has already been taken.'),
            'password.required' => __('validation.The password field is required.'),
            'password.confirmed' => __('validation.The password confirmation does not match.'),
            'country_id.required' => __('validation.The country field is required.'),
            'country_id.size' => __('validation.The country code must be exactly 3 characters.'),
            'terms.required' => __('validation.You must accept the terms and conditions.'),
            'terms.accepted' => __('validation.You must accept the terms and conditions.'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->country_id) {
            $this->merge([
                'country_id' => strtoupper($this->country_id),
            ]);
        }
    }

    /**
     * Get the validated data with processing.
     */
    public function getValidatedData(): array
    {
        $validated = $this->validated();
        unset($validated['terms']); // Remove terms as we don't store it
        return $validated;
    }
}
