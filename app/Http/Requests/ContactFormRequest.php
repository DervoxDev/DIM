<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret_key'),
                'response' => $this->get('g-recaptcha-response'),
                'remoteip' => $this->ip()
            ]);

            $body = $response->json();
            
            // Log the response for debugging
            Log::info('reCAPTCHA Response:', [
                'status' => $response->status(),
                'body' => $body,
                'secret_key_length' => strlen(config('services.recaptcha.secret_key')),
                'response_token_length' => strlen($this->get('g-recaptcha-response')),
                'ip' => $this->ip()
            ]);

            if (!$response->successful()) {
                Log::error('reCAPTCHA HTTP Request Failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return false;
            }

            return $body['success'] ?? false;

        } catch (\Exception $e) {
            Log::error('reCAPTCHA Validation Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
