@extends('components.layouts.app')

@section('title', __('auth.Profile'))

@push('styles')
<style>
    /* Profile Edit Form Styles - Same as Register */
    .profile-section {
        min-height: calc(100vh - 80px);
        padding: 120px 20px 80px;
        background-image: url('https://kde.org/reusable-assets/home-blur.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        align-items: flex-start;
        justify-content: center;
    }

    .profile-container {
        max-width: 800px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        margin-top: 2rem;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 2rem;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 1.5rem;
    }

    .profile-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .profile-header p {
        color: var(--text-color);
        font-size: 0.95rem;
    }

    .form-section {
        margin-bottom: 3rem;
    }

    .section-header {
        margin-bottom: 1.5rem;
    }

    .section-header h2 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .section-header p {
        color: var(--text-color);
        font-size: 0.875rem;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .form-input, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: white;
    }

    .form-input:focus, .form-select:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(88, 101, 242, 0.1);
    }

    /* Dropdown arrow styles - FIXED */
    .form-select {
        cursor: pointer;
        background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iOCIgdmlld0JveD0iMCAwIDEyIDgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xIDFMNiA2TDExIDEiIHN0cm9rZT0iIzZCNzI4MCIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+');
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 12px;
        padding-right: 2.5rem;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    .rtl .form-select {
        background-position: left 0.75rem center;
        padding-right: 1rem;
        padding-left: 2.5rem;
    }

    .error-message {
        color: #e53e3e;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .success-message {
        color: #48bb78;
        font-size: 0.875rem;
        margin-top: 0.5rem;
    }

    .save-button {
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-right: 1rem;
    }

    .save-button:hover {
        background: var(--accent-color);
        transform: translateY(-1px);
    }

    .verification-notice {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .verification-notice p {
        color: #92400e;
        font-size: 0.875rem;
        margin: 0;
    }

    .verification-link {
        color: var(--primary-color);
        text-decoration: underline;
        font-weight: 500;
    }

    .verification-link:hover {
        color: var(--accent-color);
    }

    /* Dark mode styles */
    .dark .profile-container {
        background: rgba(30, 32, 34, 0.95);
    }

    .dark .form-label {
        color: #e2e8f0;
    }

    .dark .form-input,
    .dark .form-select {
        background: rgba(30, 32, 34, 0.8);
        border-color: #4a5568;
        color: white;
    }

    .dark .form-input:focus,
    .dark .form-select:focus {
        border-color: var(--primary-color);
    }

    .dark .profile-header h1,
    .dark .section-header h2 {
        color: white;
    }

    .dark .profile-header p,
    .dark .section-header p {
        color: #e2e8f0;
    }

    .dark .profile-header {
        border-color: #4a5568;
    }

    .dark .verification-notice {
        background: #451a03;
        border-color: #92400e;
    }

    .dark .verification-notice p {
        color: #fbbf24;
    }

    /* RTL Support */
    .rtl {
        direction: rtl;
        text-align: right;
        font-family: 'Arial', 'Tahoma', sans-serif;
    }

    .rtl .form-input,
    .rtl .form-select {
        text-align: right;
    }

    .rtl .save-button {
        margin-right: 0;
        margin-left: 1rem;
    }

    /* Responsive styles */
    @media (max-width: 640px) {
        .profile-container {
            padding: 1.5rem;
            margin-top: 1rem;
        }

        .profile-header h1 {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<section class="profile-section">
    <div class="profile-container {{ app()->getLocale() === 'ar' ? 'rtl' : '' }}">
        <div class="profile-header">
            <h1>{{ __('auth.Profile') }}</h1>
            <p>{{ __('auth.Manage your account settings and preferences') }}</p>
        </div>

        <!-- Profile Information Section -->
        <div class="form-section">
            <div class="section-header">
                <h2>{{ __('auth.Profile Information') }}</h2>
                <p>{{ __('auth.Update your account profile information and email address') }}</p>
            </div>

            <form method="post" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')

                <!-- Name Field -->
                <div class="form-group">
                    <label for="name" class="form-label">{{ __('auth.Full Name') }}</label>
                    <input id="name" 
                           type="text" 
                           name="name" 
                           class="form-input" 
                           value="{{ old('name', $user->name) }}" 
                           required 
                           autofocus>
                    @error('name')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email" class="form-label">{{ __('auth.Email Address') }}</label>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           class="form-input" 
                           value="{{ old('email', $user->email) }}" 
                           required>
                    @error('email')
                        <p class="error-message">{{ $message }}</p>
                    @enderror

                    @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                        <div class="verification-notice">
                            <p>
                                {{ __('auth.Your email address is unverified') }}
                                <a href="#" 
                                   class="verification-link"
                                   onclick="document.getElementById('send-verification').submit(); return false;">
                                    {{ __('auth.Click here to re-send the verification email') }}
                                </a>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="success-message" style="margin-top: 0.5rem;">
                                    {{ __('auth.A new verification link has been sent to your email address') }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Country Selection -->
                <div class="form-group">
                    <label for="country_id" class="form-label">{{ __('auth.Country') }}</label>
                    
                    <select id="country_id" name="country_id" class="form-select">
                        <option value="">{{ __('auth.Select your country') }}</option>
                        @php
                            $countries = \App\Helpers\CountryHelper::getCountriesForDropdown();
                        @endphp
                        @foreach($countries as $code => $name)
                            <option value="{{ $code }}" {{ old('country_id', $user->country_id) == $code ? 'selected' : '' }}>
                                {{ $name }}
                            </option>
                        @endforeach
                    </select>
                    
                    @error('country_id')
                        <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Save Button -->
                <div style="display: flex; align-items: center; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="save-button">{{ __('auth.Save') }}</button>

                    @if (session('status') === 'profile-updated')
                        <p class="success-message">
                            {{ session('message', __('auth.Saved')) }}
                        </p>
                    @endif
                </div>
            </form>
        </div>

        <!-- Update Password Section -->
        <div class="form-section">
            <div class="section-header">
                <h2>{{ __('auth.Update Password') }}</h2>
                <p>{{ __('auth.Ensure your account is using a long random password to stay secure') }}</p>
            </div>

            @include('profile.partials.update-password-form')
        </div>

        <!-- Delete Account Section -->
        <div class="form-section">
            <div class="section-header">
                <h2>{{ __('auth.Delete Account') }}</h2>
                <p>{{ __('auth.Once your account is deleted all of its resources and data will be permanently deleted') }}</p>
            </div>

            @include('profile.partials.delete-user-form')
        </div>
    </div>

    <!-- Email Verification Form -->
    <form id="send-verification" method="post" action="{{ route('verification.send') }}" style="display: none;">
        @csrf
    </form>
</section>
@endsection
