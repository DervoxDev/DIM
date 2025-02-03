@extends('components.layouts.dervox')

@section('title', 'Solutions - Dervox')

@push('styles')
    @vite(['resources/css/dervox.css', 'resources/css/solutions.css'])
@endpush

@section('content')
    <!-- Solutions Hero Section -->
    <div class="solutions-hero">
        <div>
            <h1>Our Solutions</h1>
            <p>Discover our innovative SaaS solutions designed to transform your business operations and drive growth</p>
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
                <h1>DIM - Digital Inventory Management</h1>
                <p class="solution-description">
                    A comprehensive digital inventory management solution that streamlines your business operations and enhances efficiency. Our platform provides real-time insights and powerful tools to optimize your inventory management process.
                </p>
                
                <!-- Features and Benefits Grid -->
                <div class="features-benefits-container">
                    <!-- Key Features Card -->
                    <div class="feature-benefit-card">
                        <h4>Key Features:</h4>
                        <ul>
                            <li>
                                <i class="icon ion-ios-checkmark-circle"></i>
                                Real-time inventory tracking
                            </li>
                            <li>
                                <i class="icon ion-ios-checkmark-circle"></i>
                                Automated stock alerts
                            </li>
                            <li>
                                <i class="icon ion-ios-checkmark-circle"></i>
                                Analytics and reporting
                            </li>
                            <li>
                                <i class="icon ion-ios-checkmark-circle"></i>
                                Multi-location support
                            </li>
                            <li>
                                <i class="icon ion-ios-checkmark-circle"></i>
                                Integration capabilities
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Benefits Card -->
                    <div class="feature-benefit-card">
                        <h4>Benefits:</h4>
                        <ul>
                            <li>
                                <i class="icon ion-ios-star"></i>
                                Increased efficiency
                            </li>
                            <li>
                                <i class="icon ion-ios-star"></i>
                                Cost reduction
                            </li>
                            <li>
                                <i class="icon ion-ios-star"></i>
                                Better decision making
                            </li>
                            <li>
                                <i class="icon ion-ios-star"></i>
                                Improved accuracy
                            </li>
                            <li>
                                <i class="icon ion-ios-star"></i>
                                Time savings
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Call to Action -->
                <div class="solution-cta">
                    <a href="https://dim.dervox.com" class="button">Learn More</a>
                </div>
            </div>
        </div>

        <!-- Coming Soon Section -->
        <div class="coming-soon-section">
            <h3>More Solutions Coming Soon</h3>
            <p>We're constantly developing new solutions to meet your business needs. Stay tuned!</p>
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
