@extends('components.layouts.dervox')

@section('title', 'Services - Dervox')

@push('styles')
    @vite(['resources/css/dervox.css', 'resources/css/services.css'])
@endpush

@section('content')
    <!-- Process Work Section -->
    <div id="process-work" class="process-work section">
        <div class="container">
            <div class="section-title">
                <h5 class="title-top">Process</h5>
                <h3>Process Our Work</h3>
            </div>
            <div class="process-grid">
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-chatboxes"></i>
                        <h5>Chat</h5>
                        <p>Engage in meaningful conversations to understand your needs.</p>
                    </div>
                </div>
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-cash"></i>
                        <h5>Transaction</h5>
                        <p>Seamless transactions ensuring efficiency and security.</p>
                    </div>
                </div>
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-search"></i>
                        <h5>Research</h5>
                        <p>In-depth research driving informed decisions and innovation.</p>
                    </div>
                </div>
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-checkmark-circle"></i>
                        <h5>Deal</h5>
                        <p>Negotiating successful deals that drive mutual growth.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div id="services" class="services section-bottom-only">
        <div class="section-title">
            <h5 class="title-top">Services</h5>
            <h3>Our The Best Services</h3>
        </div>
        <div class="container">
            <div class="services-grid">
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fa-solid fa-terminal"></i>
                            <span class="clone-icon">
                                <i class="fa-solid fa-terminal"></i>
                            </span>
                        </div>
                        <h5>Cutting-Edge Programming</h5>
                        <p>Elevate your business with innovative, scalable software solutions.</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fa-solid fa-desktop"></i>
                            <span class="clone-icon">
                                <i class="fa-solid fa-desktop"></i>
                            </span>
                        </div>
                        <h5>End-to-End Services</h5>
                        <p>Complete services from start to finish, tailored to your needs.</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-cogs"></i>
                            <span class="clone-icon">
                                <i class="fas fa-cogs"></i>
                            </span>
                        </div>
                        <h5>Enterprise Solutions</h5>
                        <p>Tailored solutions to empower your business at every level.</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-tasks"></i>
                            <span class="clone-icon">
                                <i class="fas fa-tasks"></i>
                            </span>
                        </div>
                        <h5>Project Management Excellence</h5>
                        <p>Delivering exceptional project management for seamless execution.</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-bullhorn"></i>
                            <span class="clone-icon">
                                <i class="fas fa-bullhorn"></i>
                            </span>
                        </div>
                        <h5>Marketing with Impact</h5>
                        <p>Driving impactful marketing strategies that deliver results.</p>
                    </div>
                </div>
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-paint-brush"></i>
                            <span class="clone-icon">
                                <i class="fas fa-paint-brush"></i>
                            </span>
                        </div>
                        <h5>Design That Speaks</h5>
                        <p>Delivering tailored solutions for your business needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/4.5.6/css/ionicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
