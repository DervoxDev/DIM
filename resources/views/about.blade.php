@extends('components.layouts.dervox')

@section('title', 'About - Dervox')

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
                        <img src="{{ Vite::asset('resources/images/about-img.png') }}" alt="About Us" />
                    </div>
                </div>
                <div class="content-side">
                    <div class="content">
                        <h3>Why Choose Us?</h3>
                        <p>
                            At Dervox, we harness the power of technology to drive growth and innovation. Our tailored solutions help businesses overcome challenges, optimize processes, and stay competitive. We're committed to delivering sustainable, impactful results that support your long-term success, making us more than just a technology provider— we're your strategic partner in shaping the future.
                        </p>
                        <ul>
                            <li><span class="circle-list"></span>Innovative solutions tailored to your needs</li>
                            <li><span class="circle-list"></span>Cutting-edge technology to keep you ahead</li>
                            <li><span class="circle-list"></span>Dedicated support for long-term success</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>

        // Show loader when navigating
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
