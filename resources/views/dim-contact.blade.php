@extends('components.layouts.app')

@section('title', __('contact.title'))

@section('content')
<style>
/* Contact Page Styles */
:root {
    --primary-color: #5865F2;
    --secondary-color: #23272A;
    --accent-color: #4752C4;
    --text-color: #4F5660;
    --light-color: #FFFFFF;
    --background-color: #F6F6F6;
}

/* Contact Hero Section */
.contact-hero {
    background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
    padding: 120px 0 60px;
    text-align: center;
    color: var(--light-color);
    margin-bottom: 60px;
    margin-top: 80px; /* Account for fixed navbar */
}

.contact-hero h1 {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 20px;
}

.contact-hero p {
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
    opacity: 0.9;
}

/* Contact Section */
.contact-section {
    padding: 80px 2rem;
    background: var(--light-color);
}

.section-title {
    text-align: center;
    margin-bottom: 60px;
    max-width: 600px;
    margin: 0 auto 60px;
}

.section-title .title-top {
    color: var(--primary-color);
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 10px;
}

.section-title h3 {
    color: var(--secondary-color);
    font-size: 2rem;
    font-weight: 700;
    margin: 0;
}

/* Contact Grid */
.contact-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    max-width: 1200px;
    margin: 0 auto;
    align-items: start;
}

/* Contact Info */
.contact-info {
    padding-right: 40px;
    text-align: center;
    margin-top: 0;
}

.info-item {
    margin-bottom: 30px;
}

.info-item h5 {
    color: var(--primary-color);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.info-item p {
    color: var(--text-color);
    font-size: 1rem;
    line-height: 1.6;
    margin: 0;
}

/* Contact Form */
.contact-form {
    background: var(--light-color);
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    padding: 30px;
    margin-top: 0;
}

/* Form Elements */
.form-group {
    position: relative;
    margin-bottom: 1.5rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    transition: all 0.3s ease;
    background-color: var(--light-color);
    font-size: 0.875rem;
    color: var(--text-color);
}

.form-control::placeholder {
    color: #9ca3af;
}

/* Invalid state */
.form-control.is-invalid {
    border-color: #dc2626;
    background-color: #fff5f5;
}

.form-control.is-invalid::placeholder {
    color: #dc2626;
}

/* Valid state */
.form-control.is-valid {
    border-color: #059669;
    background-color: #f0fdf4;
}

/* Focus states */
.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(88, 101, 242, 0.1);
}

.form-control.is-invalid:focus {
    border-color: #dc2626;
    box-shadow: 0 0 0 2px rgba(220, 38, 38, 0.1);
}

.form-control.is-valid:focus {
    border-color: #059669;
    box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.1);
}

textarea.form-control {
    min-height: 120px;
    resize: vertical;
}

/* Submit Button */
.submit-button {
    background: var(--primary-color);
    color: var(--light-color);
    padding: 12px 30px;
    border: none;
    border-radius: 5px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
    height: 48px; /* Fixed height to prevent jumping */
    display: flex;
    align-items: center;
    justify-content: center;
}

.submit-button:hover {
    background: var(--accent-color);
}

.submit-button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.submit-button.loading {
    pointer-events: none;
    opacity: 0.7;
}

.submit-button .button-loader {
    display: none;
}

.submit-button.loading .button-text {
    display: none;
}

.submit-button.loading .button-loader {
    display: block;
}

.button-loader i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Social Links */
.social-links {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--background-color);
    color: var(--primary-color);
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-links a:hover {
    background: var(--primary-color);
    color: var(--light-color);
}

/* reCAPTCHA */
.g-recaptcha {
    margin: 20px 0;
    display: flex;
    justify-content: center;
}

/* Form Messages */
.form-messages {
    margin-bottom: 20px;
}

.form-message {
    position: relative;
    padding: 1rem;
    border-radius: 4px;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    font-size: 0.875rem;
    animation: slideDown 0.3s ease-out;
}

.form-message i {
    margin-right: 0.5rem;
    font-size: 1rem;
}

.form-message.success {
    background-color: #f0fdf4;
    border: 1px solid #bbf7d0;
    color: #15803d;
}

.form-message.error {
    background-color: #fef2f2;
    border: 1px solid #fecaca;
    color: #dc2626;
}

.close-message {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: currentColor;
    opacity: 0.5;
    cursor: pointer;
    padding: 0.25rem;
    font-size: 0.875rem;
}

.close-message:hover {
    opacity: 1;
}

/* Animation for messages */
@keyframes slideDown {
    from {
        transform: translateY(-10px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Dark Mode */
.dark .contact-section {
    background: var(--secondary-color);
}

.dark .section-title h3 {
    color: var(--light-color);
}

.dark .contact-form {
    background: #1a1b1e;
}

.dark .form-control {
    background: #2d2f34;
    border-color: #373a40;
    color: var(--light-color);
}

.dark .info-item p {
    color: #999;
}

.dark .social-links a {
    background: #2d2f34;
    color: var(--light-color);
}

.dark .social-links a:hover {
    background: var(--primary-color);
}

.dark .form-message.success {
    background-color: rgba(20, 83, 45, 0.2);
    border-color: #15803d;
    color: #bbf7d0;
}

.dark .form-message.error {
    background-color: rgba(153, 27, 27, 0.2);
    border-color: #dc2626;
    color: #fecaca;
}

/* RTL Support */
[dir="rtl"] .contact-grid,
html[lang="ar"] .contact-grid {
    direction: rtl;
}

[dir="rtl"] .contact-info,
html[lang="ar"] .contact-info {
    padding-right: 0;
    padding-left: 40px;
    text-align: right;
}

[dir="rtl"] .form-control,
html[lang="ar"] .form-control {
    text-align: right;
}

[dir="rtl"] .form-message i,
html[lang="ar"] .form-message i {
    margin-right: 0;
    margin-left: 0.5rem;
}

[dir="rtl"] .close-message,
html[lang="ar"] .close-message {
    right: auto;
    left: 1rem;
}

[dir="rtl"] .social-links a,
html[lang="ar"] .social-links a {
    margin-right: 0;
    margin-left: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .contact-grid {
        grid-template-columns: 1fr;
        padding: 0 20px;
    }

    .contact-info {
        padding-right: 0;
    }

    .contact-form {
        padding: 30px 20px;
    }

    .contact-hero {
        padding: 100px 20px 50px;
    }

    .contact-hero h1 {
        font-size: 2rem;
    }

    .contact-section {
        padding: 40px 1rem;
    }

    [dir="rtl"] .contact-info,
    html[lang="ar"] .contact-info {
        padding-left: 0;
    }

    .form-control {
        font-size: 16px; /* Prevent zoom on mobile */
    }
}

/* Accessibility */
.form-control:focus-visible {
    outline: 2px solid var(--primary-color);
    outline-offset: 2px;
}

.submit-button:focus-visible {
    outline: 2px solid var(--light-color);
    outline-offset: 2px;
}

/* Form disabled state */
.form-control:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    background-color: #f3f4f6;
}

.dark .form-control:disabled {
    background-color: #374151;
}
</style>

<!-- Contact Hero -->
<div class="contact-hero">
    <div class="">
        <h1>{{ __('contact.hero.title') }}</h1>
        <p>{{ __('contact.hero.description') }}</p>
    </div>
</div>

<!-- Contact Section -->
<section class="contact-section">
    <div class="">
        <div class="section-title">
            <h5 class="title-top">{{ __('contact.section_title') }}</h5>
            <h3>{{ __('contact.subtitle') }}</h3>
        </div>

        <div class="contact-grid">
            <!-- Contact Information -->
            <div class="contact-info">
                <div class="info-item">
                    <h5>{{ __('contact.address.title') }}</h5>
                    <p>{{ __('contact.address.value') }}</p>
                </div>

                <div class="info-item">
                    <h5>{{ __('contact.phone.title') }}</h5>
                    <p>{{ __('contact.phone.value') }}</p>
                </div>

                <div class="info-item">
                    <h5>{{ __('contact.email.title') }}</h5>
                    <p>{{ __('contact.email.value') }}</p>
                </div>

                <!-- Social Links -->
                <div class="social-links">
                    <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <div class="form-messages">
                    @if(session('success'))
                        <div class="form-message success">
                            <i class="fas fa-check-circle"></i>
                            {{ session('success') }}
                            <button type="button" class="close-message" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                    
                    @if($errors->any())
                        <div class="form-message error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $errors->first() }}
                            <button type="button" class="close-message" onclick="this.parentElement.remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    @endif
                </div>

                <form action="{{ route('contact.send') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <input type="text" 
                               class="form-control {{ $errors->has('name') ? 'is-invalid' : (old('name') ? 'is-valid' : '') }}" 
                               name="name" 
                               placeholder="{{ __('contact.form.name') }}"
                               value="{{ old('name') }}" 
                               required>
                    </div>

                    <div class="form-group">
                        <input type="email" 
                               class="form-control {{ $errors->has('email') ? 'is-invalid' : (old('email') ? 'is-valid' : '') }}" 
                               name="email" 
                               placeholder="{{ __('contact.form.email') }}"
                               value="{{ old('email') }}" 
                               required>
                    </div>

                    <div class="form-group">
                        <input type="text" 
                               class="form-control {{ $errors->has('subject') ? 'is-invalid' : (old('subject') ? 'is-valid' : '') }}" 
                               name="subject" 
                               placeholder="{{ __('contact.form.subject') }}"
                               value="{{ old('subject') }}" 
                               required>
                    </div>

                    <div class="form-group">
                        <textarea class="form-control {{ $errors->has('message') ? 'is-invalid' : (old('message') ? 'is-valid' : '') }}" 
                                  name="message" 
                                  rows="5"
                                  placeholder="{{ __('contact.form.message') }}"
                                  required>{{ old('message') }}</textarea>
                    </div>

                    <div class="form-group">
                        <div class="g-recaptcha" 
                            data-sitekey="{{ config('services.recaptcha.site_key') }}"
                            data-callback="onRecaptchaSuccess"
                            data-expired-callback="onRecaptchaExpired">
                        </div>
                    </div>

                    <button type="submit" class="submit-button" id="submitButton">
                        <span class="button-text">{{ __('contact.form.submit') }}</span>
                        <span class="button-loader" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const submitButton = document.getElementById('submitButton');
        
        // Add loading state on form submit
        document.querySelector('form').addEventListener('submit', function() {
            submitButton.classList.add('loading');
            submitButton.disabled = true;
        });

        // reCAPTCHA handlers
        window.onRecaptchaSuccess = function() {
            const recaptchaError = document.querySelector('.g-recaptcha + .form-label.is-invalid');
            if (recaptchaError) {
                recaptchaError.remove();
            }
        };

        window.onRecaptchaExpired = function() {
            grecaptcha.reset();
        };
    });
</script>

@if(session('form_submitted'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const submitButton = document.getElementById('submitButton');
        const formInputs = form.querySelectorAll('input, textarea, button');

        // Re-enable form
        submitButton.classList.remove('loading');
        formInputs.forEach(input => {
            input.disabled = false;
        });
        form.classList.remove('form-loading');
    });
</script>
@endif

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endsection
