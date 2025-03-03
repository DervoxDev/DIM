@extends('components.layouts.dervox')

@section('title', __('about.title'))

@push('styles')
    @vite(['resources/css/dervox.css', 'resources/css/about.css'])
@endpush

@section('content')
    <!-- About Us Section -->
    <div id="about" class="about section">
        <div class="container">
            <div class="row">
                <div class="image-side">
                    <div class="content-image">
                        <img src="{{ Vite::asset('resources/images/about-img.png') }}" alt="{{ __('about.title') }}" />
                    </div>
                </div>
                <div class="content-side">
                    <div class="content">
                        <h3>{{ __('about.why_choose_us') }}</h3>
                        <p>
                            {{ __('about.main_content') }}
                        </p>
                        <ul>
                            <li><span class="circle-list"></span>{{ __('about.features.innovative') }}</li>
                            <li><span class="circle-list"></span>{{ __('about.features.cutting_edge') }}</li>
                            <li><span class="circle-list"></span>{{ __('about.features.dedicated') }}</li>
                        </ul>
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
