@extends('components.layouts.dervox')

@section('title', 'Dervox - Your Tech Solutions Partner')

@push('styles')
    @vite(['resources/css/dervox.css'])
@endpush

@section('content')
    <!-- Home intro -->
    <div class="home-intro">
        <div class="container">
            <div class="row">
                <div class="content">
                    <h2><span class="color-highlight">Welcome to Dervox</span></h2>
                    <h2>Your Tech Solutions <span class="color-highlight">Partner</span></h2>
                    <p>
                        At Dervox, we go beyond being a mere technology 
                        provider—we are your dedicated ally in mastering the digital world. Our expertise lies in crafting customized solutions designed to address the specific challenges and demands of businesses and enterprises.
                    </p>
                    <a href="#contact" class="button">Contact Us</a>
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
                        <h4>Let's work together on your</h4>
                        <h4>best project</h4>
                    </div>
                    <div class="button-wrap">
                        <a href="#contact" class="button">Contact Us</a>
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
                        }, 500); // Adjust time as needed
                    }
                });
            });
        });
    </script>
@endpush