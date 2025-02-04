@extends('components.layouts.dervox')

@section('title', __('contact.title'))

@push('styles')
    @vite(['resources/css/dervox.css', 'resources/css/contact.css'])
@endpush

@section('content')
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
                        <!-- Contact Form -->
                        <div class="contact-form">
                            <!-- Alerts Container -->
                            <div class="alerts-container">
                                @if(session('success'))
                                    <div class="alert alert-success fade-in">
                                        <i class="fas fa-check-circle"></i>
                                        {{ session('success') }}
                                        <button type="button" class="close-alert" onclick="this.parentElement.style.display='none'">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif

                                @if($errors->any())
                                    <div class="alert alert-error fade-in">
                                        <i class="fas fa-exclamation-circle"></i>
                                        <ul>
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                        <button type="button" class="close-alert" onclick="this.parentElement.style.display='none'">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>


                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   name="name" 
                                   placeholder="{{ __('contact.form.name') }}"
                                   value="{{ old('name') }}" 
                                   required>
                            @error('name')
                                <div class="alert alert-error fade-in">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   name="email" 
                                   placeholder="{{ __('contact.form.email') }}"
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="alert alert-error fade-in">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <input type="text" 
                                   class="form-control @error('subject') is-invalid @enderror" 
                                   name="subject" 
                                   placeholder="{{ __('contact.form.subject') }}"
                                   value="{{ old('subject') }}" 
                                   required>
                            @error('subject')
                                <div class="alert alert-error fade-in">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      name="message" 
                                      rows="5"
                                      placeholder="{{ __('contact.form.message') }}"
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="alert alert-error fade-in">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="recaptcha-container">
                            <div class="g-recaptcha" 
                                data-sitekey="{{ config('services.recaptcha.site_key') }}"
                                data-callback="onRecaptchaSuccess"
                                data-expired-callback="onRecaptchaExpired">
                            </div>
                            @error('g-recaptcha-response')
                                <div class="alert alert-error fade-in">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="submit-button">
                            {{ __('contact.form.submit') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Link click handler
        const links = document.querySelectorAll('a');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!this.href.includes('#')) {
                    e.preventDefault();
                    document.getElementById('loader').style.display = 'flex';
                    setTimeout(() => {
                        window.location = this.href;
                    }, 500);
                }
            });
        });

        // Auto-hide alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        }, 5000);
    });
        const closeButtons = document.querySelectorAll('.close-alert');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.parentElement;
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 300);
        });
    });
    });

    // reCAPTCHA handlers
    function onRecaptchaSuccess() {
        console.log('reCAPTCHA validated successfully');
    }

    function onRecaptchaExpired() {
        grecaptcha.reset();
    }

    // Reset reCAPTCHA after form submission
    document.querySelector('form').addEventListener('submit', function() {
        setTimeout(() => {
            grecaptcha.reset();
        }, 1000);
    });
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
