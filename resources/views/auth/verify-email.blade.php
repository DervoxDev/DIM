@extends('components.layouts.app')

@section('title', __('auth.Verify Email'))

@push('styles')
<style>
    /* Verify Email Styles */
    .verify-email-section {
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

    .verify-email-container {
        max-width: 600px;
        width: 100%;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 12px;
        padding: 2.5rem;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .verify-email-header {
        text-align: center;
        margin-bottom: 2rem;
    }

    .verify-email-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .verify-email-content {
        font-size: 1rem;
        line-height: 1.6;
        color: var(--text-color);
        margin-bottom: 2rem;
    }

    .status-message {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
        background-color: #c6f6d5;
        color: #2f855a;
        border: 1px solid #9ae6b4;
        text-align: center;
    }

    .verification-actions {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 2rem;
        flex-wrap: wrap;
    }

    .resend-button {
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 2rem;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .resend-button:hover {
        background: var(--accent-color);
        transform: translateY(-1px);
    }

    .logout-button {
        background: transparent;
        color: var(--text-color);
        padding: 0.75rem 2rem;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .logout-button:hover {
        background: #f7fafc;
        border-color: var(--text-color);
    }

    .email-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 1.5rem;
        color: var(--primary-color);
    }

    /* Dark mode styles */
    .dark .verify-email-container {
        background: rgba(30, 32, 34, 0.95);
    }

    .dark .verify-email-header h1 {
        color: white;
    }

    .dark .verify-email-content {
        color: #e2e8f0;
    }

    .dark .status-message {
        background-color: rgba(198, 246, 213, 0.1);
        color: #9ae6b4;
        border-color: #2f855a;
    }

    .dark .logout-button {
        color: #e2e8f0;
        border-color: #4a5568;
    }

    .dark .logout-button:hover {
        background: rgba(247, 250, 252, 0.1);
        border-color: #e2e8f0;
    }

    /* Responsive styles */
    @media (max-width: 640px) {
        .verify-email-container {
            padding: 1.5rem;
        }

        .verify-email-header h1 {
            font-size: 1.75rem;
        }

        .verification-actions {
            flex-direction: column;
        }

        .resend-button, .logout-button {
            width: 100%;
        }
    }
</style>
@endpush

@section('content')
<section class="verify-email-section">
    <div class="verify-email-container">
        <div class="verify-email-header">
            <svg class="email-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <h1> {{ __('auth.Verify Your Email Address') }}</h1>
        </div>

        <div class="verify-email-content">
            {{ __('auth.Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="status-message">
                {{ __('auth.A new verification link has been sent to the email address you provided during registration.') }}
            </div>
        @endif

        <div class="verification-actions">
            <form method="POST" action="{{ route('verification.send') }}" class="w-full">
                @csrf
                <button type="submit" class="resend-button w-full">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    {{ __('auth.Resend Verification Email') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button type="submit" class="logout-button w-full">
                    {{ __('auth.Log Out') }}
                </button>
            </form>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    // Optional: Add animation for status message
    document.addEventListener('DOMContentLoaded', function() {
        const statusMessage = document.querySelector('.status-message');
        if (statusMessage) {
            statusMessage.style.opacity = '0';
            statusMessage.style.transform = 'translateY(-10px)';
            statusMessage.style.transition = 'all 0.3s ease';
            
            setTimeout(() => {
                statusMessage.style.opacity = '1';
                statusMessage.style.transform = 'translateY(0)';
            }, 100);
        }
    });
</script>
@endpush
