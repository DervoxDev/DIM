@extends('components.layouts.dervox')

@section('title', __('solutions.title'))

@push('styles')
    @vite(['resources/css/dervox.css', 'resources/css/solutions.css'])
@endpush

@section('content')
    <!-- Solutions Hero Section -->
    <div class="solutions-hero">
        <div>
            <h1>{{ __('solutions.hero.title') }}</h1>
            <p>{{ __('solutions.hero.description') }}</p>
        </div>
    </div>

    <div class="solutions-wrapper">
        <!-- Solution Card -->
        <div class="solution-card">
            <!-- Swiper Slider -->
            <div class="swiper progress-slide-carousel swiper-container">
                <div class="swiper-wrapper">
                    <div class="swiper-slide">
                        <div class="slider-image">
                            <img src="{{ Vite::asset('resources/images/dark.png') }}" alt="DIM Preview 1">
                        </div>
                    </div>
                    <div class="swiper-slide">
                        <div class="slider-image">
                            <img src="{{ Vite::asset('resources/images/light.png') }}" alt="DIM Preview 2">
                        </div>
                    </div>
                </div>
                <div class="swiper-pagination"></div>
            </div>

            <!-- Solution Content -->
            <div class="solution-content">
                <h1>{{ __('solutions.dim.title') }}</h1>
                <p class="solution-description">
                    {{ __('solutions.dim.description') }}
                </p>
                
                <!-- Features and Benefits Grid -->
                <div class="features-benefits-container">
                    <!-- Key Features Card -->
                    <div class="feature-benefit-card">
                        <h4>{{ __('solutions.dim.features.title') }}</h4>
                        <ul>
                            @foreach(['tracking', 'alerts', 'analytics', 'multi_location', 'integration'] as $feature)
                            <li>
                                <i class="icon ion-ios-checkmark-circle"></i>
                                {{ __("solutions.dim.features.items.$feature") }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    
                    <!-- Benefits Card -->
                    <div class="feature-benefit-card">
                        <h4>{{ __('solutions.dim.benefits.title') }}</h4>
                        <ul>
                            @foreach(['efficiency', 'cost', 'decision', 'accuracy', 'time'] as $benefit)
                            <li>
                                <i class="icon ion-ios-star"></i>
                                {{ __("solutions.dim.benefits.items.$benefit") }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="solution-cta">
                    <a href="https://dim.dervox.com" class="button">{{ __('solutions.learn_more') }}</a>
                </div>
            </div>
        </div>

        <!-- Coming Soon Section -->
        <div class="coming-soon-section">
            <h3>{{ __('solutions.coming_soon.title') }}</h3>
            <p>{{ __('solutions.coming_soon.description') }}</p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Swiper
        var swiper = new Swiper(".progress-slide-carousel", {
            loop: true,
            autoplay: {
                delay: 1200,
                disableOnInteraction: false,
            },
            pagination: {
                el: ".progress-slide-carousel .swiper-pagination",
                type: "progressbar",
            },
        });

        // Page Loader for Links
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
