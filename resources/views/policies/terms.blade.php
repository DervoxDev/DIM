@extends('components.layouts.app')

@section('title', __('policies.terms.title'))

@section('content')
<section class="policy-section" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container">
        <div class="policy-container">
            <h1>{{ __('policies.terms.title') }}</h1>
            <p class="last-updated">{{ __('policies.terms.last_updated') }}: 2023-05-23</p>
            
            <div class="policy-content">
                @foreach(range(1, 10) as $item)
                <section class="policy-item">
                    <h2>{{ $item }}. {{ __('policies.terms.items.' . $item . '.title') }}</h2>
                    <p>{!! __('policies.terms.items.' . $item . '.content') !!}</p>
                </section>
                @endforeach
            </div>
            
            <div class="policy-footer">
                <p>&copy; {{ date('Y') }} Dervox Company. {{ __('policies.common.all_rights_reserved') }}.</p>
                <p>
                    <a href="{{ route('policies.privacy') }}">{{ __('policies.terms.privacy_policy') }}</a>
                </p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .policy-section {
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 5rem 0;
        background-color: var(--light-color, #f9f9f9);
        min-height: calc(100vh - 250px); /* Adjust this value based on header/footer height */
    }
    
    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        justify-content: center;
    }
    
    .policy-container {
        max-width: 800px;
        width: 100%;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        padding: 2.5rem;
    }
    
    .policy-container h1 {
        text-align: center;
        color: var(--primary-color, #4a6cf7);
        margin-bottom: 0.5rem;
    }
    
    .last-updated {
        text-align: center;
        color: #666;
        margin-bottom: 2rem;
        font-size: 0.9rem;
    }
    
    .policy-content {
        margin-bottom: 2rem;
    }
    
    .policy-item {
        margin-bottom: 1.5rem;
    }
    
    .policy-item h2 {
        color: var(--secondary-color, #181c31);
        font-size: 1.3rem;
        margin-bottom: 0.75rem;
    }
    
    .policy-item p {
        line-height: 1.6;
        margin-bottom: 0.5rem;
        color: #555;
    }
    
    .policy-item a {
        color: var(--primary-color, #4a6cf7);
        text-decoration: none;
    }
    
    .policy-item a:hover {
        text-decoration: underline;
    }
    
    .policy-footer {
        text-align: center;
        color: #888;
        font-size: 0.9rem;
        border-top: 1px solid #eee;
        padding-top: 1.5rem;
        margin-top: 2rem;
    }
    
    .policy-footer a {
        color: var(--primary-color, #4a6cf7);
        text-decoration: none;
        margin: 0 0.5rem;
    }
    
    .policy-footer a:hover {
        text-decoration: underline;
    }

    /* RTL Support */
    [dir="rtl"] .policy-container {
        text-align: right;
    }

    [dir="rtl"] .policy-item ul {
        padding-right: 20px;
        padding-left: 0;
    }

    @media (max-width: 768px) {
        .policy-section {
            padding: 3rem 0;
            min-height: auto;
        }
        
        .policy-container {
            padding: 1.5rem;
            margin: 0 1rem;
        }
    }
</style>
@endpush
