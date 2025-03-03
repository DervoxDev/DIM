@extends('components.layouts.dervox')

@section('title', __('home.title'))

@push('styles')
    @vite(['resources/css/dervox.css'])
@endpush

@section('content')
    <!-- Home intro -->
    <div class="home-intro">
        <div class="container">
            <div class="row">
                <div class="content">
                    <h2><span class="color-highlight">{{ __('home.welcome') }}</span></h2>
                    <h2>{{ __('home.partner_title') }} <span class="color-highlight">{{ __('home.partner_highlight') }}</span></h2>
                    <p>
                        {{ __('home.description') }}
                    </p>
                    <a href="#contact" class="button">{{ __('home.contact_us') }}</a>
                </div>
                <div class="content-image">
                    <img src="{{ Vite::asset('resources/images/header-img.png') }}" alt="Hero Image" />
                </div>
            </div>
        </div>
    </div>

    <!-- Words section -->
    <div class="words-section section-bottom-only">
        <div class="container">
            <div class="content">
                <div class="row">
                    <div class="words-wrap">
                        <h4>{{ __('home.work_together') }}</h4>
                        <h4>{{ __('home.best_project') }}</h4>
                    </div>
                    <div class="button-wrap">
                        <a href="#contact" class="button">{{ __('home.contact_us') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
@endpush
