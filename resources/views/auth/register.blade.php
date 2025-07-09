@extends('components.layouts.app')

@section('title', __('auth.Register'))
@push('styles')
<style>
    /* Register Form Styles */
    .register-section {
        min-height: calc(100vh - 80px);
        padding: 120px 20px 80px;
        background-image: url('https://kde.org/reusable-assets/home-blur.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .register-container {
        max-width: 550px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .register-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .register-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .register-header p {
        color: var(--text-color);
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

    .form-select {
        cursor: pointer;
        background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIiIGhlaWdodD0iOCIgdmlld0JveD0iMCAwIDEyIDgiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxwYXRoIGQ9Ik0xIDFMNiA2TDExIDEiIHN0cm9rZT0iIzZCNzI4MCIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KPC9zdmc+');
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 12px;
        padding-right: 2.5rem;
        appearance: none;
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

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 2rem;
    }

    .login-link {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.3s ease;
    }

    .login-link:hover {
        color: var(--accent-color);
    }

    .register-button {
        background: var(--primary-color);
        color: white;
        padding: 0.70rem 1rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .register-button:hover {
        background: var(--accent-color);
        transform: translateY(-1px);
    }

    /* Password strength indicator */
    .password-strength {
        margin-top: 0.5rem;
        font-size: 0.875rem;
    }

    .strength-meter {
        height: 4px;
        background: #e2e8f0;
        border-radius: 2px;
        margin-top: 0.5rem;
    }

    .strength-meter div {
        height: 100%;
        border-radius: 2px;
        transition: all 0.3s ease;
    }

    .weak { width: 33.33%; background: #e53e3e; }
    .medium { width: 66.66%; background: #ecc94b; }
    .strong { width: 100%; background: #48bb78; }

    /* Dark mode styles */
    .dark .register-container {
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

    .dark .register-header h1 {
        color: white;
    }

    .dark .register-header p {
        color: #e2e8f0;
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

    .rtl .form-footer {
        flex-direction: row-reverse;
        justify-content: space-between;
    }

    /* Responsive styles */
    @media (max-width: 640px) {
        .register-container {
            padding: 1.5rem;
        }

        .form-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        .register-button {
            width: 100%;
        }
    }

    /* Checkbox styles */
    .checkbox-group {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1.5rem;
    }

    .form-checkbox {
        margin-right: 10px;
        margin-top: 2px;
    }

    .rtl .form-checkbox {
        margin-right: 0;
        margin-left: 10px;
    }

    .checkbox-label {
        font-size: 0.875rem;
        color: var(--text-color);
    }

    .terms-link {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
    }

    .terms-link:hover {
        text-decoration: underline;
    }

    /* Modal styles - keeping existing styles */
    .terms-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
    }

    .terms-modal-content {
        background-color: #ffffff;
        margin: 5% auto;
        padding: 0;
        width: 80%;
        max-width: 800px;
        max-height: 80vh;
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .terms-modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: var(--primary-color);
        color: white;
    }

    .terms-modal-header h2 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .close-modal {
        color: white;
        font-size: 1.75rem;
        font-weight: bold;
        cursor: pointer;
        transition: color 0.3s;
    }

    .close-modal:hover {
        color: #f1f1f1;
    }

    .terms-modal-body {
        padding: 2rem;
        overflow-y: auto;
        flex-grow: 1;
        line-height: 1.6;
    }

    .terms-modal-body h3 {
        margin-top: 0;
        color: var(--secondary-color);
        font-weight: 700;
        font-size: 1.25rem;
    }

    .terms-modal-body h4 {
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        color: var(--secondary-color);
        font-weight: 600;
        font-size: 1.1rem;
    }

    .terms-modal-body p {
        margin-bottom: 1rem;
        font-size: 0.95rem;
        color: var(--text-color);
    }

    .terms-modal-footer {
        padding: 1rem 2rem;
        background-color: #f8fafc;
        border-top: 1px solid #e2e8f0;
        text-align: right;
    }

    .rtl .terms-modal-footer {
        text-align: left;
    }

    .accept-terms-button {
        background-color: var(--primary-color);
        color: white;
        padding: 0.70rem 1.5rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
    }

    .accept-terms-button:hover {
        background-color: var(--accent-color);
        transform: translateY(-1px);
    }

    /* Dark mode adjustments for modal */
    .dark .terms-modal-content {
        background-color: #1e2022;
        color: #e2e8f0;
    }

    .dark .terms-modal-header {
        border-color: #4a5568;
    }

    .dark .terms-modal-body h3,
    .dark .terms-modal-body h4 {
        color: #e2e8f0;
    }

    .dark .terms-modal-body p {
        color: #cbd5e0;
    }

    .dark .terms-modal-footer {
        background-color: #2d3748;
        border-color: #4a5568;
    }
</style>
@endpush

@section('content')
<section class="register-section">
    <div class="register-container {{ app()->getLocale() === 'ar' ? 'rtl' : '' }}">
        <div class="register-header">
            <h1>{{ __('auth.Create Account') }}</h1>
            <p>{{ __('auth.Join our community and start exploring') }}</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">{{ __('auth.Full Name') }}</label>
                <input id="name" 
                       type="text" 
                       name="name" 
                       class="form-input" 
                       value="{{ old('name') }}" 
                       required 
                       autofocus>
                @error('name')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">{{ __('auth.Email Address') }}</label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       class="form-input" 
                       value="{{ old('email') }}" 
                       required>
                @error('email')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <!-- Country Selection - Required Field -->
            <div class="form-group">
                <label for="country_id" class="form-label">{{ __('auth.Country') }}</label>
                
                <select id="country_id" name="country_id" class="form-select" required>
                    <option value="">{{ __('auth.Select your country') }}</option>
                    @php
                        $countries = \App\Helpers\CountryHelper::getCountriesForDropdown();
                    @endphp
                    @foreach($countries as $code => $name)
                        <option value="{{ $code }}" {{ old('country_id') == $code ? 'selected' : '' }}>
                            {{ $name }}
                        </option>
                    @endforeach
                </select>
                
                @error('country_id')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('auth.Password') }}</label>
                <input id="password" 
                       type="password" 
                       name="password" 
                       class="form-input" 
                       required>
                <div class="password-strength">
                    <div class="strength-meter">
                        <div></div>
                    </div>
                </div>
                @error('password')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation" class="form-label">{{ __('auth.Confirm Password') }}</label>
                <input id="password_confirmation" 
                       type="password" 
                       name="password_confirmation" 
                       class="form-input" 
                       required>
            </div>

            <div class="form-group checkbox-group">
                <input type="checkbox" name="terms" id="terms" class="form-checkbox" required>
                <label for="terms" class="checkbox-label">
                    {!! __('auth.I agree to the') !!} <a href="#" class="terms-link" id="openTermsModal">{!! __('auth.Terms and Conditions') !!}</a>
                </label>
                @error('terms')
                    <p class="error-message">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-footer">
                <a href="{{ route('login') }}" class="login-link">
                    {{ __('auth.Already have an account? Sign in') }}
                </a>
                <button type="submit" class="register-button">
                    {{ __('auth.Create Account') }}
                </button>
            </div>
        </form>
    </div>

    <!-- Terms and Conditions Modal (keeping existing modal code) -->
    <div class="terms-modal" id="termsModal">
        <div class="terms-modal-content {{ app()->getLocale() === 'ar' ? 'rtl' : '' }}">
            <div class="terms-modal-header">
                <h2>{{ __('auth.Terms and Conditions') }}</h2>
                <span class="close-modal" id="closeTermsModal">&times;</span>
            </div>
            <div class="terms-modal-body">
                <h3>{{ __('auth.DIM - Inventory Management System') }}</h3>
                <p>{{ __('auth.Last Updated') }}: {{ date('Y-m-d') }}</p>
                
                <h4>1. {{ __('auth.Acceptance of Terms') }}</h4>
                <p>{!! __('auth.terms_acceptance') !!}</p>
                
                <h4>2. {{ __('auth.Description of Service') }}</h4>
                <p>{!! __('auth.terms_description') !!}</p>
                
                <h4>3. {{ __('auth.User Registration') }}</h4>
                <p>{!! __('auth.terms_registration') !!}</p>
                
                <h4>4. {{ __('auth.Subscription and Payments') }}</h4>
                <p>{!! __('auth.terms_subscription') !!}</p>
                
                <h4>5. {{ __('auth.Data Privacy and Security') }}</h4>
                <p>{!! __('auth.terms_privacy') !!}</p>
                
                <h4>6. {{ __('auth.User Responsibilities') }}</h4>
                <p>{!! __('auth.terms_responsibilities') !!}</p>
                
                <h4>7. {{ __('auth.Limitation of Liability') }}</h4>
                <p>{!! __('auth.terms_liability') !!}</p>
                
                <h4>8. {{ __('auth.Termination') }}</h4>
                <p>{!! __('auth.terms_termination') !!}</p>
                
                <h4>9. {{ __('auth.Governing Law') }}</h4>
                <p>{!! __('auth.terms_law') !!}</p>
                
                <h4>10. {{ __('auth.Contact Information') }}</h4>
                <p>{!! __('auth.terms_contact') !!}</p>
            </div>
            <div class="terms-modal-footer">
                <button id="acceptTerms" class="accept-terms-button">{{ __('auth.I Accept') }}</button>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthMeter = document.querySelector('.strength-meter div');
            
            // Check password strength
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            
            // Update strength meter
            strengthMeter.className = '';
            if (strength === 1) strengthMeter.classList.add('weak');
            if (strength === 2) strengthMeter.classList.add('medium');
            if (strength === 3) strengthMeter.classList.add('strong');
        });

        // Terms and conditions modal
        const modal = document.getElementById('termsModal');
        const openModalBtn = document.getElementById('openTermsModal');
        const closeModalBtn = document.getElementById('closeTermsModal');
        const acceptTermsBtn = document.getElementById('acceptTerms');
        const termsCheckbox = document.getElementById('terms');

        openModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });

        closeModalBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });

        acceptTermsBtn.addEventListener('click', function() {
            termsCheckbox.checked = true;
            modal.style.display = 'none';
            document.body.style.overflow = '';
        });

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
                document.body.style.overflow = '';
            }
        });
    });
</script>
@endpush
