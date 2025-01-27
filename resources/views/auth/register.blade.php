@extends('components.layouts.app')

@section('title', 'Register')

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
        max-width: 500px;
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
        padding: 0.75rem 2rem;
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

    .dark .form-input {
        background: rgba(30, 32, 34, 0.8);
        border-color: #4a5568;
        color: white;
    }

    .dark .form-input:focus {
        border-color: var(--primary-color);
    }

    .dark .register-header h1 {
        color: white;
    }

    .dark .register-header p {
        color: #e2e8f0;
    }

    /* Responsive styles */
    @media (max-width: 640px) {
        .register-container {
            padding: 1.5rem;
        }

        .form-footer {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
            text-align: center;
        }

        .register-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section class="register-section">
    <div class="register-container">
        <div class="register-header">
            <h1>Create Account</h1>
            <p>Join our community and start exploring</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Full Name</label>
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
                <label for="email" class="form-label">Email Address</label>
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

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
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
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input id="password_confirmation" 
                       type="password" 
                       name="password_confirmation" 
                       class="form-input" 
                       required>
            </div>

            <div class="form-footer">
                <a href="{{ route('login') }}" class="login-link">
                    Already have an account? Sign in
                </a>
                <button type="submit" class="register-button">
                    Create Account
                </button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Simple password strength checker
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
</script>
@endpush
