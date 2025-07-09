@extends('components.layouts.app')

@section('title', 'Welcome')

@push('styles')
<style>

    /* Base Button Styles */
    .button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 1.1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        min-width: 180px;
        cursor: pointer;
    }
 
    .primary-button {
        background-color: var(--light-color);
        color: var(--primary-color);
        border: none;
    }

    .primary-button:hover {
        background-color: var(--background-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .secondary-button {
        background-color: transparent;
        color: var(--light-color);
        border: 2px solid var(--light-color);
    }

    .secondary-button:hover {
        background-color: var(--light-color);
        color: var(--primary-color);
        transform: translateY(-2px);
    }
    .pricing-card .button.primary-button {
    background-color: var(--primary-color);
    color: var(--light-color);
    border: none;
}

.pricing-card .button.primary-button:hover {
    background-color: var(--accent-color);
    color: var(--light-color);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Optional: Add a special class for the pricing CTA button */
.pricing-cta-button {
    background-color: var(--primary-color) !important;
    color: var(--light-color) !important;
    border: none !important;
}

.pricing-cta-button:hover {
    background-color: var(--accent-color) !important;
    color: var(--light-color) !important;
}
   
    /* Hero Section */
    .hero {
    background-image: url('https://kde.org/reusable-assets/home-blur.jpg');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    padding: 120px 20px 80px;
    color: var(--light-color);
    position: relative;
    overflow: hidden;
    text-align: center;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}

.hero-container {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
}

.hero-content {
    text-align: center;
    margin-bottom: 2rem;
}

.hero h1 {
    font-size: 3.5rem;
    font-weight: 700;
    margin-bottom: 1.5rem;
    line-height: 1.2;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.hero p {
    font-size: 1.25rem;
    max-width: 600px;
    margin: 0 auto 2rem;
    opacity: 0.9;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.hero-image {
    max-width: 1000px; /* Increased from 600px */
    width: 100%;
    height: auto;
    margin: 2rem auto;
    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
}

.hero-buttons {
    display: flex;
    gap: 1.5rem;
    justify-content: center;
    margin-top: 2rem;
}

.hero-buttons .button {
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
/* Image Comparison Slider */
.laptop-with-overlay {
    position: relative;
    max-width: 1005px;
    margin: 1rem auto;
    width: 100%;
}

.laptop-with-overlay .laptop {
    width: 100%;
    height: 100%;
    display: block;
    filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
}

/* Update these styles */
.laptop-overlay {
    position: absolute;
    top: 2.8%;
    left: 10.3%;
    width: 79.4%;
    height: 72%;
    overflow: hidden;
    border-radius: 8px;
    background: #000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.laptop-overlay .images-compare-container {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.images-compare-before,
.images-compare-after {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}


.laptop-overlay .images-compare-before img,
.laptop-overlay .images-compare-after img {
    width: 100%;
    height: auto;
    max-height: 100%;
    object-fit: contain !important;
}
.laptop-overlay .images-compare-separator {
    background: white;
}
.laptop-overlay #imageCompare {
    height: 100%;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.laptop-overlay .images-compare-handle {
    background: white;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    position: relative;
    width: 40px;
    height: 40px;
    margin-left: -20px;
    border-radius: 50%;
}
.laptop-overlay .images-compare-left-arrow {
    position: absolute;
    left: 15px;
    top: 60%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-right: 8px solid #666;
}
.laptop-overlay .images-compare-right-arrow {
    position: absolute;
    right: 15px;
    top: 60%;
    transform: translateY(-50%);
    width: 0;
    height: 0;
    border-top: 6px solid transparent;
    border-bottom: 6px solid transparent;
    border-left: 8px solid #666;
}
.laptop-overlay .images-compare-handle::before,
.laptop-overlay .images-compare-handle::after {
    display: none;
}
.image-container {
    height: 100%;
    width: 100%;
    position: relative;
}

.image-before{
    height: 100%;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
    object-fit: contain;
}
.image-after {
    height: 100%;
    width: 100%;
    position: absolute;
    top: 0;
    left: 0;
    object-fit: contain;
}

.image-before img,
.image-after img,
.screen-image {
    width: 100%;
    height: 100%;
    object-fit: contain !important;
}

.image-after {
    width: 50%;
    overflow: hidden;
}

.comparison-slider {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 99;
}

.comparison-slider input {
    width: 100%;
    height: 100%;
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0;
    cursor: ew-resize;
}

.drag-line {
    width: 4px;
    height: 100%;
    position: absolute;
    left: 50%;
    pointer-events: none;
    background: #fff;
}

.drag-line span {
    height: 40px;
    width: 40px;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: #fff;
    border-radius: 50%;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}


    /* Features Section */
    .features {
        padding: 100px 20px;
        background: var(--light-color);
    }

    .features-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .feature-card {
        padding: 2rem;
        border-radius: 12px;
        background: var(--background-color);
        transition: transform 0.3s ease;
        text-align: center;
    }

    .feature-card:hover {
        transform: translateY(-5px);
    }

    .feature-icon {
        font-size: 2.5rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    /* Stats Section */
    .stats {
        background: var(--secondary-color);
        padding: 80px 20px;
        color: var(--light-color);
    }

    .stats-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        text-align: center;
    }

    .stat-item h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }

    /* Download Section */
    .download {
        padding: 100px 20px;
        background: var(--background-color);
    }

    .download-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .download-header h2 {
        font-size: 2.5rem;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .download-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .download-card {
        background: var(--light-color);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .download-card:hover {
        transform: translateY(-5px);
    }

    .os-icon {
        font-size: 3rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }

    .download-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        margin-top: auto;
        padding-top: 1.5rem;
        width: 100%;
    }

    .download-buttons .button {
        width: 100% !important;
        max-width: none !important;
    }

    .version-info {
        margin-top: 1rem;
        font-size: 0.875rem;
        color: var(--text-color);
    }

    .download-card .primary-button {
        background-color: var(--primary-color);
        color: var(--light-color);
    }
    /* Pricing Section */
    .pricing {
        padding: 100px 20px;
        background: var(--light-color);
    }

    .pricing-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .pricing-header h2 {
        font-size: 2.5rem;
        color: var(--secondary-color);
        margin-bottom: 1rem;
    }

    .pricing-grid {
        max-width: 1200px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
    }

    .pricing-card {
        background: var(--light-color);
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        position: relative;
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .pricing-card:hover {
        transform: translateY(-5px);
    }

    .pricing-card.popular {
        border: 2px solid var(--primary-color);
    }
    .coming-soon {
    opacity: 0.8;
    pointer-events: none;
}

.coming-soon-badge {
    position: absolute;
    top: -12px;
    right: 2rem;
    background: var(--text-color);
    color: var(--light-color);
    padding: 0.25rem 1rem;
    border-radius: 20px;
    font-size: 0.875rem;
}

.pricing-card.coming-soon:hover {
    transform: none;
}
    .popular-badge {
        position: absolute;
        top: -12px;
        right: 2rem;
        background: var(--primary-color);
        color: var(--light-color);
        padding: 0.25rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
    }

    .price {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin: 1rem 0;
    }

    .price span {
        font-size: 1rem;
        color: var(--text-color);
    }

    .features-list {
        list-style: none;
        margin: 2rem 0;
        text-align: left;
    }

    .features-list li {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .features-list i {
        color: var(--primary-color);
    }

    .pricing-card .button {
        margin-top: auto;
    }
    .price-tba {
    font-size: 1.5rem;
    color: var(--text-color);
}

.price-tba .price-info {
    font-size: 0.875rem;
    margin-top: 0.5rem;
    color: var(--text-color);
    opacity: 0.8;
}

.custom-price {
    font-size: 1.5rem;
    color: var(--text-color);
}

.price-notify {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    padding: 1rem 0;
}

.notify-form {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 1rem;
}

.notify-input {
    padding: 0.5rem 1rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    width: 100%;
}

.notify-button {
    background: var(--primary-color);
    color: white;
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.notify-button:hover {
    background: var(--accent-color);
}

/* Dark mode styles */
.dark .notify-input {
    background: rgba(30, 32, 34, 0.8);
    border-color: #4a5568;
    color: white;
}

.dark .price-tba,
.dark .custom-price {
    color: #e2e8f0;
}
.scroll-to-top {
    position: fixed;
    right: 25px;
    top: 50%; /* Change from bottom to top */
    transform: translateY(-50%); /* Add this to center vertically */
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background-color: #007bff; /* Use a specific color instead of var() */
    color: #ffffff;
    text-align: center;
    line-height: 45px;
    font-size: 20px;
    cursor: pointer;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: none;
    z-index: 9999;
    transition: all 0.3s ease;
    opacity: 0.9;
}

.scroll-to-top:hover {
    background-color: #0056b3;
    transform: translateY(-50%) scale(1.1); /* Modified to maintain vertical centering */
    opacity: 1;
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
}

/* Dark mode support if needed */
.dark .scroll-to-top {
    background-color: var(--light-color);
    color: var(--primary-color);
}

.dark .scroll-to-top:hover {
    background-color: var(--background-color);
}
    /* CTA Section */
    .cta {
        padding: 100px 20px;
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        color: var(--light-color);
        text-align: center;
    }

    .cta-container {
        max-width: 800px;
        margin: 0 auto;
    }

    .cta h2 {
        font-size: 2.5rem;
        margin-bottom: 1.5rem;
    }
  .cta p {
        
        margin-bottom: 1.5rem;
    }
    .cta .primary-button {
        background-color: var(--light-color);
        color: var(--primary-color);
        font-weight: 600;
        padding: 1.2rem 2.5rem;
    }
    @media (max-width: 1024px) {
    .hero-image {
        max-width: 80%;
    }
}

    /* Responsive Design */
    @media (max-width: 768px) {
        .button {
            width: 100%;
            max-width: 300px;
        }

        .hero {
        padding: 80px 20px 60px;
    }

    .scroll-to-top {
        right: 20px;
        width: 40px;
        height: 40px;
        line-height: 40px;
        font-size: 18px;
    }

    .hero h1 {
        font-size: 2.5rem;
    }

    .hero-image {
        max-width: 90%;
        margin: 1.5rem auto;
    }

    .hero-buttons {
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .hero-buttons .button {
        width: 100%;
    }

        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .download-grid {
            gap: 1.5rem;
        }

        .pricing-grid {
            gap: 2rem;
        }

        .features-grid {
            gap: 1.5rem;
        }

        .laptop-with-overlay {
        margin: 1rem auto;
    }

    .laptop-overlay {
        border-radius: 4px;
    }

    }

    @media (max-width: 480px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }

        .hero h1 {
        font-size: 2rem;
    }
    .hero-image {
        max-width: 100%;
        margin: 1rem auto;
    }
        .download-card, .pricing-card {
            padding: 1.5rem;
        }
    }




   /* Pricing toggle styles */
    .billing-toggle {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2rem auto 0;
        gap: 0.5rem;
    }

    .billing-option {
        font-weight: 500;
        color: var(--text-color);
        opacity: 0.7;
        transition: all 0.3s ease;
    }

    .billing-option.active {
        opacity: 1;
        color: var(--primary-color);
        font-weight: 600;
    }

    .billing-save {
        background: var(--primary-color);
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        margin-left: 0.5rem;
    }

    /* Switch styling */
    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .switch input { 
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
    }

    input:checked + .slider {
        background-color: var(--primary-color);
    }

    input:focus + .slider {
        box-shadow: 0 0 1px var(--primary-color);
    }

    input:checked + .slider:before {
        transform: translateX(26px);
    }

    .slider.round {
        border-radius: 24px;
    }

    .slider.round:before {
        border-radius: 50%;
    }
    
    /* Price subtext */
    .price-subtext {
        font-size: 0.8rem;
        color: var(--text-color);
        margin-top: -0.5rem;
        margin-bottom: 1rem;
        opacity: 0.8;
    }
    
    /* Comparison table styles */
    .pricing-comparison {
        max-width: 1200px;
        margin: 4rem auto 0;
          margin-bottom: 2rem;
    }
    
.pricing-comparison h3 {
    text-align: center;
        font-size: 2.5rem;
        color: var(--secondary-color);
        margin-bottom: 1rem;
}
    
    .comparison-table-container {
        overflow-x: auto;
    }
    
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .comparison-table th,
    .comparison-table td {
        padding: 1rem;
        text-align: center;
        border: 1px solid #eaeaea;
    }
    
    .comparison-table th {
        background: var(--primary-color);
        color: var(--light-color);
        font-weight: 600;
    }
    
    .comparison-table th:first-child {
        text-align: left;
    }
    
    .comparison-table td:first-child {
        text-align: left;
        font-weight: 500;
    }
    
    .comparison-table tr:nth-child(even) {
        background-color: rgba(0, 0, 0, 0.02);
    }
    
    .text-success {
        color: #28a745;
    }
    
    .text-danger {
        color: #dc3545;
    }
    
    /* Make the pricing cards consistent height */
    .pricing-grid {
        grid-auto-rows: 1fr;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    }
    
    /* Dark mode support */
    .dark .comparison-table th {
        background: var(--accent-color);
    }
    
    .dark .comparison-table td {
        border-color: #333;
    }
    
    .dark .comparison-table tr:nth-child(even) {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    @media (max-width: 992px) {
        .pricing-grid {
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        }
    }

.total-price.small-text {
    font-size: 14px;
    color: #666;
    margin-top: 5px;
    font-weight: normal;
}
/* RTL Support for pricing toggle */
[dir="rtl"] .billing-toggle {
    flex-direction: row; /* Maintain same direction even in RTL */
}

[dir="rtl"] .billing-save {
    margin-right: 0.5rem;
    margin-left: 0;
}

/* RTL Support for toggle switch */
[dir="rtl"] .switch {
    direction: ltr; /* Force LTR direction for the switch itself */
    transform: scaleX(-1); /* Mirror it horizontally */
}

[dir="rtl"] .switch input:checked + .slider:before {
    transform: translateX(26px); /* Maintain same sliding behavior */
}

/* For comparison table */
[dir="rtl"] .comparison-table th:first-child,
[dir="rtl"] .comparison-table td:first-child {
    text-align: right;
}

/* Fix the billing toggle layout in RTL */
[dir="rtl"] .billing-option {
    display: inline-block; /* Ensure text stays in correct position */
}

</style>
@endpush

@section('content')
<!-- Hero Section -->
<!-- In your hero section, replace the simple img tag with this: -->
<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <div class="hero-content">
            <h1>{{ __('messages.Transform Your Business with Our SaaS Solution') }}</h1>
            <p>{{ __('messages.Streamline your operations, boost productivity, and scale your business with our powerful platform.') }}</p>
        </div>
        <div class="laptop-with-overlay">
    <img class="laptop" src="{{ Vite::asset('resources/images/laptop.svg') }}"  alt="Laptop" width="2000" height="1220">
    <div class="laptop-overlay">
        <div id="imageCompare">
            <!-- The first div will be the front element -->
            <div style="display: none;">
                <img src="{{ Vite::asset('resources/images/light.png') }}" alt="Light Theme">
            </div>
            <!-- This div will be the back element -->
            <div>
                <img src="{{ Vite::asset('resources/images/dark.png') }}" alt="Dark Theme">
            </div>
        </div>
    </div>
</div>


        <div class="hero-buttons">
            <a href="{{ route('register') }}" class="button primary-button">{{ __('messages.Get Started Free') }}</a>
            <a href="#features" class="button secondary-button">{{ __('messages.Learn More') }}</a>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="features">
    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-rocket"></i>
            </div>
            <h3>{{ __('messages.Quick Setup') }}</h3>
            <p>{{ __('messages.Get started in minutes with our intuitive setup process.') }}</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h3>{{ __('messages.Secure Platform') }}</h3>
            <p>{{ __('messages.Enterprise-grade security to protect your valuable data.') }}</p>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h3>{{ __('messages.Analytics') }}</h3>
            <p>{{ __('messages.Detailed insights to help you make informed decisions.') }}</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="stats-grid">
        <div class="stat-item">
            <h3>10K+</h3>
            <p>{{ __('messages.Users') }}</p>
        </div>
        <div class="stat-item">
            <h3>99.9%</h3>
            <p>{{ __('messages.Uptime') }}</p>
        </div>
        <div class="stat-item">
            <h3>24/7</h3>
            <p>{{ __('messages.Support') }}</p>
        </div>
        <div class="stat-item">
            <h3>50+</h3>
            <p>{{ __('messages.Countries') }}</p>
        </div>
    </div>
</section>

<!-- Download Section -->
<section id="download" class="download">
    <div class="download-header">
        <h2>{{ __('messages.Download') }} {{ config('app.name') }}</h2>
        <p>{{ __('messages.Choose your platform and start using our platform') }}</p>
    </div>
    <div class="download-grid">
        <!-- Windows -->
        <div class="download-card">
            <div class="os-icon">
                <i class="fab fa-windows"></i>
            </div>
            <h3>Windows</h3>
            <p>Windows 8.1 or higher</p>
            <div class="download-buttons">
                <a href="{{ route('download.os', 'windows-64') }}" class="button primary-button">
                    {{ __('messages.Download 64-bit') }}
                </a>
                <!-- <a href="{{ route('download.os', 'windows-32 ') }}" class="button primary-button">
                    {{ __('messages.Download 32-bit') }}
                </a> -->
                                <!-- <a href="javascript:void(0)" 
                class="button primary-button disabled" 
                style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                    {{ __('messages.Download 32-bit') }} ({{ __('messages.Coming Soon') }})
                </a> -->
            </div>
            <div class="version-info">Version 1.0.0 • 84.5 MB</div>
        </div>

        <!-- macOS -->
        <div class="download-card">
            <div class="os-icon">
                <i class="fab fa-apple"></i>
            </div>
            <h3>macOS</h3>
            <p>macOS 10.13 or higher</p>
            <div class="download-buttons">
                <!-- <a href="{{ route('download.os', 'mac') }}" class="button primary-button">
                    {{ __('messages.Download for macOS') }}
                </a> -->
                <a href="javascript:void(0)" 
                class="button primary-button disabled" 
                style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                    {{ __('messages.Download for macOS') }} ({{ __('messages.Coming Soon') }})
                </a>
            </div>
            <div class="version-info">Version 1.0.0 • 72.3 MB</div>
        </div>

        <!-- Linux -->
        <div class="download-card">
            <div class="os-icon">
                <i class="fab fa-linux"></i>
            </div>
            <h3>Linux</h3>
            <p>Ubuntu, Fedora & more</p>
            <div class="download-buttons">
                <!-- <a href="{{ route('download.os', ['os' => 'linux', 'type' => 'Appimage']) }}" class="button primary-button">
                    {{ __('messages.Download .Appimage') }}
                </a> -->
                <a href="javascript:void(0)" 
                class="button primary-button disabled" 
                style="opacity: 0.5; cursor: not-allowed; pointer-events: none;">
                    {{ __('messages.Download .Appimage') }} ({{ __('messages.Coming Soon') }})
                </a>
            </div>
            <div class="version-info">Version 1.0.0 • ~64 MB</div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
<section id="pricing" class="pricing">
    <div class="pricing-header">
        <h2>{{ __('messages.Choose Your Plan') }}</h2>
        <p>{{ __('messages.Start with our free trial and grow with us') }}</p>
        
        <!-- Billing Toggle -->
        <div class="billing-toggle">
            <span class="billing-option">{{ __('messages.6 Months') }}</span>
            <label class="switch">
                <input type="checkbox" id="billingToggle">
                <span class="slider round"></span>
            </label>
            <span class="billing-option active">{{ __('messages.Yearly') }}</span>
            <span class="billing-save">{{ __('messages.Save 8%') }}</span>
        </div>
    </div>
    
    <div class="pricing-grid">
        <!-- Free Trial Plan -->
        <div class="pricing-card popular">
            <div class="popular-badge">{{ __('messages.Available Now') }}</div>
            <h3>{{ __('messages.Free Trial') }}</h3>
            <div class="price">
                <div>0 <span class="currency">{{ __('messages.MAD') }}</span><span>/{{ __('messages.month') }}</span></div>
            </div>
            <ul class="features-list">
                <li><i class="fas fa-check"></i> {{ __('messages.Full Access to All Features') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Priority Support') }}</li>
                <li><i class="fas fa-clock"></i> {{ __('messages.14-Day Trial Period') }}</li>
            </ul>

            <a href="{{ route('register') }}" class="button primary-button pricing-cta-button">{{ __('messages.Start Free Trial') }}</a>
        </div>

        <!-- Basic Plan (Enabled) -->
        <div class="pricing-card">
            <div class="popular-badge">{{ __('messages.Available Now') }}</div>
            <h3>{{ __('messages.Basic') }}</h3>
     <div class="price">
    <div class="biannual-price">400 <span class="currency">{{ __('messages.MAD') }}</span><span>/{{ __('messages.month') }}</span></div>
    <div class="annual-price" style="display: none;">367 <span class="currency">{{ __('messages.MAD') }}</span><span>/{{ __('messages.month') }}</span></div>
    
    <div class="total-price biannual-text small-text">{{ __('messages.Billed every 6 months') }}: 2,400 <span class="currency">{{ __('messages.MAD') }}</span></div>
    <div class="total-price annual-text small-text" style="display: none;">{{ __('messages.Billed annually') }}: 4,400 <span class="currency">{{ __('messages.MAD') }}</span></div>
</div>

            <ul class="features-list">
                
                <li><i class="fas fa-check"></i> {{ __('messages.Up to 2 Team Members') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Basic Analytics') }}</li>
            </ul>
            <a href="#placeOrder" class="button primary-button pricing-cta-button" data-plan="basic">{{ __('messages.Place an Order') }}</a>
        </div>

        <!-- Pro Plan (Coming Soon) -->
        <div class="pricing-card coming-soon">
            <div class="coming-soon-badge">{{ __('messages.Coming Soon') }}</div>
            <h3>{{ __('messages.Pro') }}</h3>
            <div class="price price-tba">
                <span>{{ __('messages.Price TBA') }}</span>
                <p class="price-info">{{ __('messages.Sign up to be notified') }}</p>
            </div>
            <ul class="features-list opacity-75">
                <li><i class="fas fa-check"></i> {{ __('messages.All Basic Features') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Up to 5 Team Members') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Advanced Analytics') }}</li>
            </ul>
            <button disabled class="button secondary-button opacity-75">
                {{ __('messages.Coming Soon') }}
            </button>
        </div>

        <!-- Enterprise Plan (Coming Soon) -->
        <div class="pricing-card coming-soon">
            <div class="coming-soon-badge">{{ __('messages.Coming Soon') }}</div>
            <h3>{{ __('messages.Enterprise') }}</h3>
            <div class="price price-tba">
                <span>{{ __('messages.Price TBA') }}</span>
                <p class="price-info">{{ __('messages.Sign up to be notified') }}</p>
            </div>
            <ul class="features-list opacity-75">
                <li><i class="fas fa-check"></i> {{ __('messages.All Pro Features') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Unlimited Team Members') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Custom Integration') }}</li>
                <li><i class="fas fa-check"></i> {{ __('messages.Dedicated Support') }}</li>
            </ul>
            <button disabled class="button secondary-button opacity-75">
                {{ __('messages.Coming Soon') }}
            </button>
        </div>
    </div>
</section>


    
    <!-- Comparison Table -->
    <div class="pricing-comparison">
        <h3>{{ __('messages.Feature Comparison') }}</h3>
        <div class="comparison-table-container">
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.Feature') }}</th>
                        <th>{{ __('messages.Free Trial') }}</th>
                        <th>{{ __('messages.Basic') }}</th>
                        <th>{{ __('messages.Pro') }}</th>
                        <th>{{ __('messages.Enterprise') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('messages.Duration') }}</td>
                        <td>14 {{ __('messages.days') }}</td>
                        <td>{{ __('messages.Unlimited') }}</td>
                        <td>{{ __('messages.Unlimited') }}</td>
                        <td>{{ __('messages.Unlimited') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.Team Members') }}</td>
                        <td>1</td>
                        <td>1</td>
                        <td>5</td>
                        <td>{{ __('messages.Unlimited') }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.Analytics') }}</td>
                        <td>{{ __('messages.Basic') }}</td>
                        <td>{{ __('messages.Basic') }}</td>
                        <td>{{ __('messages.Advanced') }}</td>
                        <td>{{ __('messages.Custom') }}</td>
                    </tr>
               
                    <tr>
                        <td>{{ __('messages.Priority Support') }}</td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.Custom Integration') }}</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                    <tr>
                        <td>{{ __('messages.Dedicated Support') }}</td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-times text-danger"></i></td>
                        <td><i class="fas fa-check text-success"></i></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>




<!-- CTA Section -->
<section class="cta">
    <div class="cta-container">
        <h2>{{ __('messages.Ready to Get Started?') }}</h2>
        <p>{{ __('messages.Join thousands of satisfied customers who trust our platform.') }}</p>
        <a href="{{ route('register') }}" class="button primary-button">{{ __('messages.Start Free Trial') }}</a>
    </div>
</section>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const billingToggle = document.getElementById('billingToggle');
    const biannualPrices = document.querySelectorAll('.biannual-price');
    const annualPrices = document.querySelectorAll('.annual-price');
    const biannualText = document.querySelectorAll('.biannual-text');
    const annualText = document.querySelectorAll('.annual-text');
    const billingOptions = document.querySelectorAll('.billing-option');
    
    // Set default to yearly (checked)
    billingToggle.checked = true;
    updatePriceDisplay();
    
    billingToggle.addEventListener('change', updatePriceDisplay);
    
    function updatePriceDisplay() {
        if (billingToggle.checked) {
            // Show annual prices
            biannualPrices.forEach(el => el.style.display = 'none');
            annualPrices.forEach(el => el.style.display = 'block');
            biannualText.forEach(el => el.style.display = 'none');
            annualText.forEach(el => el.style.display = 'block');
            billingOptions[1].classList.add('active');
            billingOptions[0].classList.remove('active');
        } else {
            // Show biannual prices
            biannualPrices.forEach(el => el.style.display = 'block');
            annualPrices.forEach(el => el.style.display = 'none');
            biannualText.forEach(el => el.style.display = 'block');
            annualText.forEach(el => el.style.display = 'none');
            billingOptions[0].classList.add('active');
            billingOptions[1].classList.remove('active');
        }
    }
    
    // Handle place order button clicks
    document.querySelectorAll('[data-plan]').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const plan = this.getAttribute('data-plan');
            const period = billingToggle.checked ? 'annual' : 'biannual';
            
            // Here you would typically open a modal or redirect to a checkout page
            alert(`You selected the ${plan} plan with ${period} billing.`);
            
            // In a real implementation, you might use something like:
            // window.location.href = `/checkout?plan=${plan}&period=${period}`;
        });
    });


    const scrollToTopButton = document.getElementById('scrollToTop');
    
    // Show/hide button with smooth fade
    window.onscroll = function() {
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            scrollToTopButton.style.display = 'block';
            setTimeout(() => {
                scrollToTopButton.style.opacity = '0.9';
            }, 10);
        } else {
            scrollToTopButton.style.opacity = '0';
            setTimeout(() => {
                scrollToTopButton.style.display = 'none';
            }, 300);
        }
    };

    // Smooth scroll to top with easing
    scrollToTopButton.addEventListener('click', function() {
        const scrollStep = -window.scrollY / (500 / 15);
        
        const scrollInterval = setInterval(function() {
            if (window.scrollY !== 0) {
                window.scrollBy(0, scrollStep);
            } else {
                clearInterval(scrollInterval);
            }
        }, 15);
    });
    // Initialize image compare
    $('#imageCompare').imagesCompare({
    initVisibleRatio: 0.5,
    interactionMode: "drag",
    addSeparator: true,
    addDragHandle: true,
    addHandleArrows: true,
    animationDuration: 400,
    animationEasing: "swing",
    precision: 2,
    onAfterInit: function() {
        // Force contain after initialization
        $('.images-compare-before img, .images-compare-after img').css({
            'object-fit': 'contain',
            'width': '100%',
            'height': '100%'
        });
    }
});

    // Add animation when scrolling into view
    const imageCompare = $('#imageCompare').data('imagesCompare');
    
    const revealAnimation = () => {
        imageCompare.setValue(0, true, 1000); // Start with showing only dark theme
        setTimeout(() => {
            imageCompare.setValue(0.5, true, 1000); // Animate to middle
        }, 500);
    };

    // Use Intersection Observer for animation
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                revealAnimation();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    observer.observe($('#imageCompare')[0]);

    // Your existing stats animation code
    const stats = document.querySelectorAll('.stat-item h3');
    const statsSection = document.querySelector('.stats');
    
    const animateStats = () => {
        stats.forEach(stat => {
            const originalText = stat.textContent;
            const targetValue = parseFloat(originalText);
            let currentValue = 0;
            const increment = targetValue / 50;
            const duration = 2000;
            const stepTime = duration / 50;

            const updateStat = () => {
                if (currentValue < targetValue) {
                    currentValue += increment;
                    if (originalText.includes('K')) {
                        stat.textContent = Math.round(currentValue) + 'K+';
                    } else if (originalText.includes('%')) {
                        stat.textContent = currentValue.toFixed(1) + '%';
                    } else if (originalText.includes('/')) {
                        stat.textContent = '24/7';
                    } else {
                        stat.textContent = Math.round(currentValue) + '+';
                    }
                    setTimeout(updateStat, stepTime);
                } else {
                    stat.textContent = originalText;
                }
            };
            updateStat();
        });
    };

    if (statsSection) {
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateStats();
                    statsObserver.unobserve(entry.target);
                }
            });
        });

        statsObserver.observe(statsSection);
    }
    
});

</script>
@endpush
