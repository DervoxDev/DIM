@extends('components.layouts.app')


@section('title', __('auth.Login'))

@push('styles')
<style>
    .rtl {
    direction: rtl;
    text-align: right;
    font-family: 'Arial', 'Tahoma', sans-serif;
}

.rtl .form-input {
    text-align: right;
}

.rtl .form-label {
    text-align: right;
}

.rtl .remember-me {
    justify-content: flex-end; /* Pushes the content to the right */
    flex-direction: row-reverse; /* Puts checkbox after label */
}

.rtl .remember-me input[type="checkbox"] {
    margin: 0 0 0 0.5rem; /* Space between checkbox and label */
    order: 2; /* Checkbox appears after label */
}
.rtl .remember-me label {
    margin-left: 0.5rem; /* Adds space between label and checkbox */
}

.rtl .form-footer {
    flex-direction: row; /* Change to row instead of row-reverse */
}

.rtl .forgot-password {
    order: 2; /* Push forgot password link to the right */
}

.rtl .login-button {
    order: 1; /* Pull login button to the left */
    margin: 0;
}

.rtl .register-link {
    text-align: center;
}

/* Error messages in RTL */
.rtl .text-red-600 {
    text-align: right;
}
.rtl input::placeholder {
    font-family: 'Arial', 'Tahoma', sans-serif;
}

.rtl .login-header h1 {
    font-family: 'Arial', 'Tahoma', sans-serif;
    letter-spacing: normal;
}
.rtl .form-label,
.rtl .form-input,
.rtl .remember-me label,
.rtl .forgot-password,
.rtl .login-button,
.rtl .register-link {
    font-family: 'Arial', 'Tahoma', sans-serif;
    letter-spacing: normal;
}
/* Mob
    /* Login Form Styles */
    .login-section {
        min-height: calc(100vh - 80px); /* Adjust for navbar height */
        padding: 120px 20px 80px;
        background-image: url('https://kde.org/reusable-assets/home-blur.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-container {
        max-width: 450px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .login-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .login-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 0.5rem;
    }

    .login-header p {
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

    .form-input {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .form-input:focus {
        border-color: var(--primary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(88, 101, 242, 0.1);
    }

    .remember-me {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

    .remember-me input[type="checkbox"] {
        width: 1rem;
        height: 1rem;
        border-radius: 4px;
        border: 2px solid #e2e8f0;
        accent-color: var(--primary-color);
    }

    .form-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1.5rem;
    }

    .forgot-password {
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.3s ease;
    }

    .forgot-password:hover {
        color: var(--accent-color);
    }

    .login-button {
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .login-button:hover {
        background: var(--accent-color);
        transform: translateY(-1px);
    }

    .register-link {
        text-align: center;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e2e8f0;
    }

    .register-link a {
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 500;
    }

    .register-link a:hover {
        color: var(--accent-color);
    }

    /* Dark mode styles */
    .dark .login-container {
        background: rgba(30, 32, 34, 0.95);
    }

    .dark .form-label {
        color: #e2e8f0;
    }

    .dark .form-input {
        background: rgba(30, 32, 34, 0.8);
        border-color: #4a5568;
        color: white;
    }

    .dark .form-input:focus {
        border-color: var(--primary-color);
    }

    .dark .login-header h1 {
        color: white;
    }

    .dark .login-header p {
        color: #e2e8f0;
    }
    @media (max-width: 640px) {
    .rtl .form-footer {
        flex-direction: column;
        align-items: stretch;
    }

    .rtl .login-button,
    .rtl .forgot-password {
        width: 100%;
        text-align: center;
        margin: 0.5rem 0;
    }

    .rtl .forgot-password {
        order: 1; /* In mobile, forgot password goes above */
    }

    .rtl .login-button {
        order: 2; /* In mobile, button goes below */
    }
}
</style>
@endpush

@section('content')
<section class="login-section">
<div class="login-container {{ app()->getLocale() === 'ar' ? 'rtl' : '' }}">
        <div class="login-header">
            <h1>{{ __('auth.Welcome Back') }}</h1>
            <p>{{ __('auth.Sign in to continue to your account') }}</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="mb-4 text-sm font-medium text-green-600">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="form-group">
                <label for="email" class="form-label">{{ __('auth.Email Address') }}</label>
                <input id="email" 
                       type="email" 
                       name="email" 
                       class="form-input" 
                       value="{{ old('email') }}" 
                       required 
                       autofocus>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">{{ __('auth.Password') }}</label>
                <input id="password" 
                       type="password" 
                       name="password" 
                       class="form-input" 
                       required>
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="remember-me">
                <input type="checkbox" id="remember_me" name="remember">
                <label for="remember_me">{{ __('auth.Remember me') }}</label>
            </div>

            <div class="form-footer">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-password">
                    {{ __('auth.Forgot your password?') }}
                    </a>
                @endif

                <button type="submit" class="login-button">
                {{ __('auth.Log in') }}
                </button>
            </div>
        </form>

        <div class="register-link">
        {{ __("auth.Don't have an account?") }}
            <a href="{{ route('register') }}">{{ __('auth.Create one now') }}</a>
        </div>
    </div>
</section>
@endsection
