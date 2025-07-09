@extends('components.layouts.app')

@section('title', __('policies.privacy.title'))

@section('content')
<section class="policy-section" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    <div class="container">
        <div class="policy-container">
            <h1>{{ __('policies.privacy.title') }}</h1>
            <p class="last-updated">{{ __('policies.privacy.last_updated') }}: 2023-05-23</p>
            
            <div class="policy-content">
                <!-- Section 1: Introduction -->
                <section class="policy-item">
                    <h2>1. {{ __('policies.privacy.items.1.title') }}</h2>
                    <p>{!! __('policies.privacy.items.1.content') !!}</p>
                    <p>{!! __('policies.privacy.items.1.subcontent') !!}</p>
                </section>
                
                <!-- Section 2: Information We Collect -->
                <section class="policy-item">
                    <h2>2. {{ __('policies.privacy.items.2.title') }}</h2>
                    
                    <h3>2.1 {{ __('policies.privacy.items.2.subtitle1') }}</h3>
                    <p>{!! __('policies.privacy.items.2.content1') !!}</p>
                    <ul>
                        @foreach(__('policies.privacy.items.2.list1') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    
                    <h3>2.2 {{ __('policies.privacy.items.2.subtitle2') }}</h3>
                    <p>{!! __('policies.privacy.items.2.content2') !!}</p>
                    <ul>
                        @foreach(__('policies.privacy.items.2.list2') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
                
                <!-- Section 3: How We Use Your Information -->
                <section class="policy-item">
                    <h2>3. {{ __('policies.privacy.items.3.title') }}</h2>
                    <p>{!! __('policies.privacy.items.3.content') !!}</p>
                    <ul>
                        @foreach(__('policies.privacy.items.3.list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
                
                <!-- Section 4: Data Storage and Security -->
                <section class="policy-item">
                    <h2>4. {{ __('policies.privacy.items.4.title') }}</h2>
                    <p>{!! __('policies.privacy.items.4.content') !!}</p>
                    <p>{!! __('policies.privacy.items.4.subcontent') !!}</p>
                </section>
                
                <!-- Section 5: Data Retention -->
                <section class="policy-item">
                    <h2>5. {{ __('policies.privacy.items.5.title') }}</h2>
                    <p>{!! __('policies.privacy.items.5.content') !!}</p>
                </section>
                
                <!-- Section 6: Disclosure of Data -->
                <section class="policy-item">
                    <h2>6. {{ __('policies.privacy.items.6.title') }}</h2>
                    <p>{!! __('policies.privacy.items.6.content') !!}</p>
                    <ul>
                        @foreach(__('policies.privacy.items.6.list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
                
                <!-- Section 7: Your Data Protection Rights -->
                <section class="policy-item">
                    <h2>7. {{ __('policies.privacy.items.7.title') }}</h2>
                    <p>{!! __('policies.privacy.items.7.content') !!}</p>
                    <ul>
                        @foreach(__('policies.privacy.items.7.list') as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </section>
                
                <!-- Section 8: Cookies -->
                <section class="policy-item">
                    <h2>8. {{ __('policies.privacy.items.8.title') }}</h2>
                    <p>{!! __('policies.privacy.items.8.content') !!}</p>
                    <p>{!! __('policies.privacy.items.8.subcontent') !!} <a href="{{ route('policies.cookies') }}">{{ __('policies.privacy.cookie_policy') }}</a>.</p>
                </section>
                
                <!-- Section 9: Changes to This Privacy Policy -->
                <section class="policy-item">
                    <h2>9. {{ __('policies.privacy.items.9.title') }}</h2>
                    <p>{!! __('policies.privacy.items.9.content') !!}</p>
                    <p>{!! __('policies.privacy.items.9.subcontent') !!}</p>
                </section>
                
                <!-- Section 10: Contact Us -->
                <section class="policy-item">
                    <h2>10. {{ __('policies.privacy.items.10.title') }}</h2>
                    <p>{!! __('policies.privacy.items.10.content') !!}</p>
                </section>
            </div>
            
            <div class="policy-footer">
                <p>&copy; {{ date('Y') }} Dervox Company. {{ __('policies.common.all_rights_reserved') }}.</p>
                <p>
                    <a href="{{ route('policies.terms') }}">{{ __('policies.privacy.terms_and_conditions') }}</a>
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
    
    .policy-item h3 {
        font-size: 1.1rem;
        margin-bottom: 0.5rem;
        margin-top: 1rem;
        color: var(--secondary-color, #181c31);
    }
    
    .policy-item p {
        line-height: 1.6;
        margin-bottom: 0.5rem;
        color: #555;
    }
    
    .policy-item ul {
        margin-bottom: 1rem;
        padding-left: 20px;
    }
    
    .policy-item ul li {
        margin-bottom: 0.3rem;
        line-height: 1.5;
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
        
        .policy-container h1 {
            font-size: 1.8rem;
        }
        
        .policy-item h2 {
            font-size: 1.2rem;
        }
    }
</style>
@endpush
