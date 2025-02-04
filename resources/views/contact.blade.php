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
                <div class="contact-form">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-error">
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact.send') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   placeholder="{{ __('contact.form.name') }}"
                                   value="{{ old('name') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <input type="email" 
                                   class="form-control" 
                                   name="email" 
                                   placeholder="{{ __('contact.form.email') }}"
                                   value="{{ old('email') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="subject" 
                                   placeholder="{{ __('contact.form.subject') }}"
                                   value="{{ old('subject') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <textarea class="form-control" 
                                      name="message" 
                                      rows="5"
                                      placeholder="{{ __('contact.form.message') }}"
                                      required>{{ old('message') }}</textarea>
                        </div>
                        <div class="recaptcha-container">
                            <div class="g-recaptcha" 
                                data-sitekey="{{ config('services.recaptcha.site_key') }}">
                            </div>
                         </div>
                         @error('g-recaptcha-response')
                        <div class="alert alert-error">
                                    {{ $message }}
                                </div>
                          @enderror

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
        });
    </script>
    <!-- Add reCAPTCHA Script -->
     
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush