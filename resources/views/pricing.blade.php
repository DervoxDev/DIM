@extends('components.layouts.app')

@section('title', 'Pricing')

@push('styles')
<style>
    .pricing-header {
        background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
        padding: 100px 20px 50px;
        color: var(--light-color);
        text-align: center;
    }

    .pricing-header h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .pricing-container {
        max-width: 1200px;
        margin: -50px auto 100px;
        padding: 0 20px;
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
    }

    .pricing-card:hover {
        transform: translateY(-10px);
    }

    .pricing-card.popular {
        border: 2px solid var(--primary-color);
        position: relative;
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
        font-size: 3rem;
        font-weight: 700;
        color: var(--secondary-color);
        margin: 1.5rem 0;
    }

    .price span {
        font-size: 1rem;
        color: var(--text-color);
    }

    .features-list {
        list-style: none;
        margin: 2rem 0;
        padding: 0;
    }

    .features-list li {
        margin: 0.75rem 0;
        color: var(--text-color);
    }

    .features-list li i {
        color: var(--primary-color);
        margin-right: 0.5rem;
    }

    @media (max-width: 768px) {
        .pricing-container {
            margin-top: 2rem;
        }
    }
</style>
@endpush

@section('content')
<div class="pricing-header">
    <h1>{{ __('Choose Your Plan') }}</h1>
    <p>{{ __('Start free and scale as you grow') }}</p>
</div>

<div class="pricing-container">
    <!-- Free Plan -->
    <div class="pricing-card">
        <h2>{{ __('Free') }}</h2>
        <div class="price">$0<span>/month</span></div>
        <ul class="features-list">
            <li><i class="fas fa-check"></i> {{ __('Up to 5 users') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Basic features') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Community support') }}</li>
        </ul>
        <a href="{{ route('register') }}" class="button primary-button">{{ __('Get Started') }}</a>
    </div>

    <!-- Pro Plan -->
    <div class="pricing-card popular">
        <div class="popular-badge">{{ __('Popular') }}</div>
        <h2>{{ __('Pro') }}</h2>
        <div class="price">$29<span>/month</span></div>
        <ul class="features-list">
            <li><i class="fas fa-check"></i> {{ __('Up to 50 users') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Advanced features') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Priority support') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Analytics') }}</li>
        </ul>
        <a href="{{ route('register') }}?plan=pro" class="button primary-button">{{ __('Start Free Trial') }}</a>
    </div>

    <!-- Enterprise Plan -->
    <div class="pricing-card">
        <h2>{{ __('Enterprise') }}</h2>
        <div class="price">$99<span>/month</span></div>
        <ul class="features-list">
            <li><i class="fas fa-check"></i> {{ __('Unlimited users') }}</li>
            <li><i class="fas fa-check"></i> {{ __('All features') }}</li>
            <li><i class="fas fa-check"></i> {{ __('24/7 support') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Custom integration') }}</li>
            <li><i class="fas fa-check"></i> {{ __('Advanced security') }}</li>
        </ul>
        <a href="{{ route('register') }}?plan=enterprise" class="button primary-button">{{ __('Contact Sales') }}</a>
    </div>
</div>
@endsection
