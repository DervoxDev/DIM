@extends('components.layouts.dervox')

@section('title', __('services.title'))

@push('styles')
    @vite(['resources/css/dervox.css', 'resources/css/services.css'])
@endpush

@section('content')
    <!-- Process Work Section -->
    <div id="process-work" class="process-work section">
        <div class="container">
            <div class="section-title">
                <h5 class="title-top">{{ __('services.process.title') }}</h5>
                <h3>{{ __('services.process.subtitle') }}</h3>
            </div>
            <div class="process-grid">
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-chatboxes"></i>
                        <h5>{{ __('services.process.items.chat.title') }}</h5>
                        <p>{{ __('services.process.items.chat.description') }}</p>
                    </div>
                </div>
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-cash"></i>
                        <h5>{{ __('services.process.items.transaction.title') }}</h5>
                        <p>{{ __('services.process.items.transaction.description') }}</p>
                    </div>
                </div>
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-search"></i>
                        <h5>{{ __('services.process.items.research.title') }}</h5>
                        <p>{{ __('services.process.items.research.description') }}</p>
                    </div>
                </div>
                <div class="process-item">
                    <div class="content">
                        <i class="icon ion-ios-checkmark-circle"></i>
                        <h5>{{ __('services.process.items.deal.title') }}</h5>
                        <p>{{ __('services.process.items.deal.description') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Services Section -->
    <div id="services" class="services section-bottom-only">
        <div class="section-title">
            <h5 class="title-top">{{ __('services.services.title') }}</h5>
            <h3>{{ __('services.services.subtitle') }}</h3>
        </div>
        <div class="container">
            <div class="services-grid">
                <!-- Programming Service -->
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fa-solid fa-terminal"></i>
                            <span class="clone-icon">
                                <i class="fa-solid fa-terminal"></i>
                            </span>
                        </div>
                        <h5>{{ __('services.services.items.programming.title') }}</h5>
                        <p>{{ __('services.services.items.programming.description') }}</p>
                    </div>
                </div>

                <!-- End-to-End Service -->
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fa-solid fa-desktop"></i>
                            <span class="clone-icon">
                                <i class="fa-solid fa-desktop"></i>
                            </span>
                        </div>
                        <h5>{{ __('services.services.items.end_to_end.title') }}</h5>
                        <p>{{ __('services.services.items.end_to_end.description') }}</p>
                    </div>
                </div>

                <!-- Enterprise Solutions -->
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-cogs"></i>
                            <span class="clone-icon">
                                <i class="fas fa-cogs"></i>
                            </span>
                        </div>
                        <h5>{{ __('services.services.items.enterprise.title') }}</h5>
                        <p>{{ __('services.services.items.enterprise.description') }}</p>
                    </div>
                </div>

                <!-- Project Management -->
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-tasks"></i>
                            <span class="clone-icon">
                                <i class="fas fa-tasks"></i>
                            </span>
                        </div>
                        <h5>{{ __('services.services.items.project_management.title') }}</h5>
                        <p>{{ __('services.services.items.project_management.description') }}</p>
                    </div>
                </div>

                <!-- Marketing -->
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-bullhorn"></i>
                            <span class="clone-icon">
                                <i class="fas fa-bullhorn"></i>
                            </span>
                        </div>
                        <h5>{{ __('services.services.items.marketing.title') }}</h5>
                        <p>{{ __('services.services.items.marketing.description') }}</p>
                    </div>
                </div>

                <!-- Design -->
                <div class="service-item">
                    <div class="content">
                        <div class="serv-icon">
                            <i class="fas fa-paint-brush"></i>
                            <span class="clone-icon">
                                <i class="fas fa-paint-brush"></i>
                            </span>
                        </div>
                        <h5>{{ __('services.services.items.design.title') }}</h5>
                        <p>{{ __('services.services.items.design.description') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/4.5.6/css/ionicons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
@endpush

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
@endpush
