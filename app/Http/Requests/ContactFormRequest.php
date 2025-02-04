<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;

class ContactFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'g-recaptcha-response' => ['required']
        ];
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => __('contact.validation.recaptcha'),
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->validateRecaptcha()) {
                $validator->errors()->add(
                    'g-recaptcha-response',
                    __('contact.validation.recaptcha_invalid')
                );
            }
        });
    }

    protected function validateRecaptcha(): bool
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $this->get('g-recaptcha-response'),
            'remoteip' => $this->ip()
        ]);

        return $response->json('success');
    }
}
