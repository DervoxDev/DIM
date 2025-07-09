@extends('components.layouts.dervox')

@section('title', __('home.title'))

@push('styles')
    @vite(['resources/css/dervox.css'])
@endpush

@section('content')
<!-- DIM Service Banner -->
<div class="dim-banner">
    <div class="banner-bg-effects">
        <div class="floating-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
    </div>
    <div class="container" style="padding-top:80px;">
        <div class="row">
            <div class="banner-image">
                <div class="image-glow"></div>
                <div class="image-wrapper">
                    <img src="{{ Vite::asset('resources/images/image2.png') }}" alt="DIM Service" />
                </div>
                <div class="pulse-rings">
                    <div class="pulse-ring ring-1"></div>
                    <div class="pulse-ring ring-2"></div>
                    <div class="pulse-ring ring-3"></div>
                </div>
            </div>
            <div class="banner-content">
                <div class="content-wrapper">
                    <div class="badge">
                        <span>{{ __('home.new_service') }}</span>
                    </div>
                    <h3>{{ __('home.dim_title') }}</h3>
                    <p>{{ __('home.dim_description') }}</p>
                    <div class="features-list">
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>{{ __('home.feature_1') }}</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>{{ __('home.feature_2') }}</span>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-check"></i>
                            <span>{{ __('home.feature_3') }}</span>
                        </div>
                    </div>
                    <div class="button-group">
                        <a href="https://dim.dervox.com" target="_blank" class="button dim-button primary">
                            <span>{{ __('home.try_dim') }}</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        <a href="#demo" class="button dim-button secondary">
                            <span>{{ __('home.watch_demo') }}</span>
                            <i class="fas fa-play"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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
    <!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/67966e043a84273260750a53/1iihprgrp';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-images-compare@0.2.5/build/jquery.images-compare.min.js"></script>

@endpush
