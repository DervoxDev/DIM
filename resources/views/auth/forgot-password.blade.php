@extends('components.layouts.app')

@section('title', __('auth.Forgot Password?'))

@push('styles')
<style>
    .rtl {
    direction: rtl;
    text-align: right;
    font-family: 'Arial', 'Tahoma', sans-serif;
}

.rtl .forgot-password-header {
    text-align: right;
}

.rtl .forgot-password-header h1,
.rtl .forgot-password-header p {
    text-align: right;
}
.rtl .form-label {
    text-align: right;
}

.rtl .form-input {
    text-align: right;
    direction: rtl;
}


.rtl .status-message {
    text-align: right;
}

.rtl input::placeholder {
    text-align: right;
}

.rtl .back-to-login {
    text-align: right;
}

/* Font adjustments for Arabic */
.rtl,
.rtl input,
.rtl button,
.rtl .forgot-password-header h1,
.rtl .forgot-password-header p,
.rtl .form-label,
.rtl .form-input,
.rtl .status-message,
.rtl .back-to-login {
    font-family: 'Arial', 'Tahoma', sans-serif;
    letter-spacing: normal;
}

    /* Forgot Password Styles */
    .forgot-password-section {
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

    .forgot-password-container {
        max-width: 500px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .forgot-password-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .forgot-password-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .forgot-password-header p {
        color: var(--text-color);
        font-size: 1rem;
        line-height: 1.6;
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

    .submit-button {
        width: 100%;
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .submit-button:hover {
        background: var(--accent-color);
        transform: translateY(-1px);
    }

    .back-to-login {
        display: block;
        text-align: center;
        margin-top: 1.5rem;
        color: var(--primary-color);
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.3s ease;
    }

    .back-to-login:hover {
        color: var(--accent-color);
    }

    .status-message {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }

    .status-message.success {
        background-color: #c6f6d5;
        color: #2f855a;
        border: 1px solid #9ae6b4;
    }

    .status-message.error {
        background-color: #fed7d7;
        color: #c53030;
        border: 1px solid #feb2b2;
    }

    /* Dark mode styles */
    .dark .forgot-password-container {
        background: rgba(30, 32, 34, 0.95);
    }

    .dark .forgot-password-header h1 {
        color: white;
    }

    .dark .forgot-password-header p {
        color: #e2e8f0;
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

    .dark .status-message.success {
        background-color: rgba(198, 246, 213, 0.1);
        color: #9ae6b4;
        border-color: #2f855a;
    }

    .dark .status-message.error {
        background-color: rgba(254, 215, 215, 0.1);
        color: #feb2b2;
        border-color: #c53030;
    }

    /* Responsive styles */
    @media (max-width: 640px) {
        .forgot-password-container {
            padding: 1.5rem;
        }

        .forgot-password-header h1 {
            font-size: 1.75rem;
        }
        .rtl .forgot-password-container {
        padding: 1.5rem;
    }
    }
</style>
@endpush

@section('content')
<section class="forgot-password-section">
<div class="forgot-password-container {{ app()->getLocale() === 'ar' ? 'rtl' : '' }}">
        <div class="forgot-password-header">
            <h1>{{ __('auth.Forgot Password?') }}</h1>
            <p>{{ __('auth.Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}</p>
        </div>

        @if (session('status'))
            <div class="status-message success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
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
                    <div class="status-message error">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button type="submit" class="submit-button">
                {{ __('auth.Send Password Reset Link') }}
            </button>

            <a href="{{ route('login') }}" class="back-to-login">
            {{ __('auth.Back to Login') }}
            </a>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Optional: Add animation when status message appears
    document.addEventListener('DOMContentLoaded', function() {
        const statusMessages = document.querySelectorAll('.status-message');
        statusMessages.forEach(message => {
            message.style.opacity = '0';
            message.style.transform = 'translateY(-10px)';
            message.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                message.style.opacity = '1';
                message.style.transform = 'translateY(0)';
            }, 100);
        });
    });
</script>
@endpush
