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
                <div class="contact-form">
    <form action="{{ route('contact.send') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label {{ $errors->has('name') ? 'is-invalid' : (old('name') ? 'is-valid' : '') }}">
                {{ __('contact.form.name') }}
                @error('name')
                    <span class="label-error">{{ $message }}</span>
                @enderror
            </label>
            <input type="text" 
                   class="form-control {{ $errors->has('name') ? 'is-invalid' : (old('name') ? 'is-valid' : '') }}" 
                   name="name" 
                   value="{{ old('name') }}" 
                   required>
        </div>

        <div class="form-group">
            <label class="form-label {{ $errors->has('email') ? 'is-invalid' : (old('email') ? 'is-valid' : '') }}">
                {{ __('contact.form.email') }}
                @error('email')
                    <span class="label-error">{{ $message }}</span>
                @enderror
            </label>
            <input type="email" 
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : (old('email') ? 'is-valid' : '') }}" 
                   name="email" 
                   value="{{ old('email') }}" 
                   required>
        </div>

        <div class="form-group">
            <label class="form-label {{ $errors->has('subject') ? 'is-invalid' : (old('subject') ? 'is-valid' : '') }}">
                {{ __('contact.form.subject') }}
                @error('subject')
                    <span class="label-error">{{ $message }}</span>
                @enderror
            </label>
            <input type="text" 
                   class="form-control {{ $errors->has('subject') ? 'is-invalid' : (old('subject') ? 'is-valid' : '') }}" 
                   name="subject" 
                   value="{{ old('subject') }}" 
                   required>
        </div>

        <div class="form-group">
            <label class="form-label {{ $errors->has('message') ? 'is-invalid' : (old('message') ? 'is-valid' : '') }}">
                {{ __('contact.form.message') }}
                @error('message')
                    <span class="label-error">{{ $message }}</span>
                @enderror
            </label>
            <textarea class="form-control {{ $errors->has('message') ? 'is-invalid' : (old('message') ? 'is-valid' : '') }}" 
                      name="message" 
                      rows="5"
                      required>{{ old('message') }}</textarea>
        </div>

        <div class="form-group">
            <div class="g-recaptcha" 
                data-sitekey="{{ config('services.recaptcha.site_key') }}"
                data-callback="onRecaptchaSuccess"
                data-expired-callback="onRecaptchaExpired">
            </div>
            @error('g-recaptcha-response')
                <label class="form-label is-invalid">
                    <span class="label-error">{{ $message }}</span>
                </label>
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

    // Reset reCAPTCHA after form submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(() => {
            grecaptcha.reset();
        }, 1000);
    });

    });

    // reCAPTCHA handlers
    // function onRecaptchaSuccess() {
    //     console.log('reCAPTCHA validated successfully');
    // }

    // function onRecaptchaExpired() {
    //     grecaptcha.reset();
    // }

    // // Reset reCAPTCHA after form submission
    // document.querySelector('form').addEventListener('submit', function() {
    //     setTimeout(() => {
    //         grecaptcha.reset();
    //     }, 1000);
    // });
</script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush
