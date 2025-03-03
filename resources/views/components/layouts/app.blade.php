<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}" 
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - @yield('title', 'Welcome')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-images-compare@0.2.5/src/assets/css/images-compare.min.css">
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* RTL Styles for Navbar and Footer */
        [dir="rtl"] .navbar-container,
html[lang="ar"] .navbar-container {
    flex-direction: row-reverse;
    justify-content: space-between; /* Add this */
}
[dir="rtl"] .nav-links,
html[lang="ar"] .nav-links {
    flex-direction: row-reverse;
    margin-left: 0;
    margin-right: auto;
    order: 1; /* Add this */
}
[dir="rtl"] .logo,
html[lang="ar"] .logo {
    margin-left: auto; /* Add this */
    margin-right: 0; /* Add this */
    order: 2; /* Add this */
}

/* Profile dropdown RTL adjustments */
[dir="rtl"] .profile-dropdown,
html[lang="ar"] .profile-dropdown {
    margin-left: auto; /* Change this */
    margin-right: 1rem;
    order: 0; /* Add this */
}
[dir="rtl"] .profile-dropdown-content,
html[lang="ar"] .profile-dropdown-content {
    left: 0;
    right: auto;

}
[dir="rtl"] .profile-button,
html[lang="ar"] .profile-button {
    flex-direction: row-reverse;
}

[dir="rtl"] .profile-button i,
html[lang="ar"] .profile-button i {
    margin-right: 0.5rem;
    margin-left: 0;
}
/* Language switcher RTL adjustments */
dir=["rtl"] .language-switcher,
html[lang="ar"] .language-switcher {
    margin-left: auto; /* Change this */
    margin-right: 1rem;
    order: 0; /* Add this */
}

[dir="rtl"] .language-dropdown,
html[lang="ar"] .language-dropdown {
    left: 0;
    right: auto;
}

[dir="rtl"] .dropdown-item,
html[lang="ar"] .dropdown-item {
    text-align: right;
}
[dir="rtl"] .dropdown-item-content,
html[lang="ar"] .dropdown-item-content {
    left: 0;
    right: auto;
    text-align: right;
}

[dir="rtl"] .profile-dropdown-content,
html[lang="ar"] .profile-dropdown-content {
    left: 0;
    right: auto;
    text-align: right;
    
}

[dir="rtl"] .dropdown-item i,
html[lang="ar"] .dropdown-item i {
    margin-left: 0.5rem;
    margin-right: 0;
}

/* RTL Footer Styles */
[dir="rtl"] .footer-grid,
html[lang="ar"] .footer-grid {
    direction: rtl;
    text-align: right;
}

[dir="rtl"] .social-links,
html[lang="ar"] .social-links {
    justify-content: flex-start;
}

[dir="rtl"] .footer-column h4,
html[lang="ar"] .footer-column h4 {
    text-align: right;
}

[dir="rtl"] .footer-links,
html[lang="ar"] .footer-links {
    padding-right: 0;
}



.main-nav-links {
    display: flex;
    gap: 2rem;
    align-items: center;
}

[dir="rtl"] .main-nav-links,
html[lang="ar"] .main-nav-links {
    flex-direction: row-reverse;
}

[dir="rtl"] .nav-links,
html[lang="ar"] .nav-links {
    flex-direction: row-reverse;
    margin-left: 0;
    margin-right: auto;
    order: 1;
}
/* Nav Links Container */
.nav-links-container {
    display: flex;
    align-items: center;
    gap: 2rem;
    width: 100%;
    justify-content: flex-end;
}

.main-nav-group {
    display: flex;
    align-items: center;
    gap: 2rem;
}

/* RTL Styles */
[dir="rtl"] .nav-links-container,
html[lang="ar"] .nav-links-container {
    flex-direction: row-reverse;
    justify-content: flex-start;
}

[dir="rtl"] .main-nav-group,
html[lang="ar"] .main-nav-group {
    flex-direction: row;
}


        /* Reset & Base Styles */
        :root {
            --primary-color: #5865F2;
            --secondary-color: #23272A;
            --accent-color: #4752C4;
            --text-color: #4F5660;
            --light-color: #FFFFFF;
            --background-color: #F6F6F6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        main {
            flex: 1;
        }

        /* Enhanced Navbar */
        .guest-navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 1rem 2rem;
            background: var(--light-color);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        /* Profile Dropdown */
        .profile-dropdown {
            position: relative;
            display: inline-block;
        }

        .profile-button {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            color: var(--secondary-color);
            font-size: 1rem;
            font-weight: 500;
        }

        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            min-width: 200px;
            background-color: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            z-index: 1000;
        }

        .profile-dropdown.active .profile-dropdown-content {
            display: block;
        }
        .dropdown-item-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    width: 100%;
}
.dropdown-item {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: var(--secondary-color);
    text-decoration: none;
    transition: background-color 0.3s ease;
    border: none;
    width: 100%;
    text-align: left;
    font-size: 0.875rem;
    cursor: pointer;
    background: none;
}

        .dropdown-item:hover {
            background-color: var(--background-color);
            color: var(--primary-color);
        }

        /* Language Switcher */
        .language-switcher {
            position: relative;
            margin-left: 1rem;
        }

        .language-button {
            background: none;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem;
            color: var(--secondary-color);
        }

        .language-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            background: var(--light-color);
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            display: none;
            min-width: 150px;
            z-index: 1000;
        }

        .language-dropdown.active {
            display: block;
        }



        .language-option {
    display: block;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: background-color 0.3s ease;
    text-decoration: none;
    color: var(--secondary-color);
}

.language-option:hover {
    background-color: var(--background-color);
    color: var(--primary-color);
}

.language-option.active {
    background-color: var(--background-color);
    color: var(--primary-color);
}

        /* Footer */
        .footer {
            background: var(--secondary-color);
            padding: 4rem 2rem;
            color: var(--light-color);
            margin-top: auto;
        }

        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr repeat(4, 1fr);
            gap: 2rem;
        }

        .footer-brand h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }

        .social-links {
            display: flex;
            gap: 1.5rem;
            margin-top: 1rem;
        }

        .social-links a {
            color: var(--light-color);
            font-size: 1.25rem;
            transition: color 0.3s ease;
        }

        .social-links a:hover {
            color: var(--primary-color);
        }

        .footer-column h4 {
            color: var(--primary-color);
            margin-bottom: 1rem;
            font-size: 1.125rem;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 0.75rem;
        }

        .footer-links a {
            color: var(--light-color);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }

        /* Dark Mode Styles */
        .dark body {
            background-color: var(--secondary-color);
            color: var(--light-color);
        }

        .dark .guest-navbar {
            background-color: #1a1b1e;
        }

        .dark .nav-link,
        .dark .profile-button,
        .dark .language-button {
            color: var(--light-color);
        }

        .dark .profile-dropdown-content,
        .dark .language-dropdown {
            background-color: #1a1b1e;
        }

        .dark .dropdown-item:hover,
        .dark .language-option:hover {
            background-color: #2d2f34;
        }
        .mobile-menu-button {
    display: none;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.5rem;
    font-size: 1.5rem;
    color: var(--secondary-color);
}

        /* Mobile Styles */
        @media (max-width: 768px) {
            .mobile-menu-button {
                display: block;
            }

            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--light-color);
                padding: 1rem;
                flex-direction: column;
                align-items: flex-start;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }

            .nav-links.active {
                display: flex;
            }

            .footer-grid {
                grid-template-columns: 1fr;
            }
            [dir="rtl"] .profile-dropdown-content,
    html[lang="ar"] .profile-dropdown-content {
        width: 100%;
    }
            [dir="rtl"] .nav-links,
    html[lang="ar"] .nav-links {
        text-align: right;
        align-items: flex-end; /* Change this */
        right: 0;
        left: 0;
    }


    [dir="rtl"] .mobile-menu-button,
    html[lang="ar"] .mobile-menu-button {
        margin-left: auto;
        margin-right: 0;
        order: 0; /* Add this */
    }
    
    [dir="rtl"] .language-switcher,
    html[lang="ar"] .language-switcher {
        margin-right: 0;
    }
    [dir="rtl"] .nav-links > *,
    html[lang="ar"] .nav-links > * {
        width: 100%;
        text-align: right;
    }
    .main-nav-links {
        flex-direction: column;
        width: 100%;
        gap: 1rem;
    }

    [dir="rtl"] .main-nav-links,
    html[lang="ar"] .main-nav-links {
        align-items: flex-end;
    }


      .nav-links-container {
        flex-direction: column;
        width: 100%;
    }

    .main-nav-group {
        flex-direction: column;
        width: 100%;
        gap: 1rem;
    }

    [dir="rtl"] .nav-links-container,
    html[lang="ar"] .nav-links-container {
        align-items: flex-end;
    }

    [dir="rtl"] .main-nav-group,
    html[lang="ar"] .main-nav-group {
        align-items: flex-end;
    }
        }
        /* Cookie Consent Banner */
.cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 -4px 10px rgba(0, 0, 0, 0.1);
    z-index: 10000;
    border-top: 1px solid rgba(0, 0, 0, 0.1);
    padding: 1rem;
    font-size: 0.9rem;
}

.cookie-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    gap: 2rem;
}

.cookie-container.rtl {
    direction: rtl;
    text-align: right;
}

.cookie-content {
    flex: 1;
}

.cookie-content h3 {
    font-size: 1.25rem;
    margin-bottom: 0.5rem;
    color: var(--secondary-color);
}

.cookie-content p {
    margin-bottom: 0;
    color: var(--text-color);
}

.cookie-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.cookie-button {
    padding: 0.5rem 1rem;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.85rem;
    border: 1px solid transparent;
    transition: all 0.2s ease;
}

.cookie-button.accept {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.cookie-button.accept:hover {
    background-color: var(--accent-color);
    border-color: var(--accent-color);
}

.cookie-button.decline {
    background-color: white;
    color: var(--text-color);
    border-color: #e2e8f0;
}

.cookie-button.decline:hover {
    background-color: #f8fafc;
    border-color: #cbd5e0;
}

.cookie-button.settings {
    background-color: transparent;
    color: var(--text-color);
    border-color: transparent;
    text-decoration: underline;
}

.cookie-button.settings:hover {
    color: var(--primary-color);
}

/* Cookie Settings Modal */
.cookie-modal {
    position: fixed;
    z-index: 10001;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
}

.cookie-modal-content {
    background-color: white;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
}

.cookie-modal-content.rtl {
    direction: rtl;
    text-align: right;
}

.cookie-modal-header {
    padding: 1.25rem;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.cookie-modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: var(--secondary-color);
}

.cookie-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-color);
}

.cookie-modal-body {
    padding: 1.5rem;
}

.cookie-option {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1.5rem;
    gap: 1rem;
}

.cookie-option-text h4 {
    margin-top: 0;
    margin-bottom: 0.5rem;
    font-size: 1rem;
    color: var(--secondary-color);
}

.cookie-option-text p {
    margin: 0;
    font-size: 0.875rem;
    color: var(--text-color);
}

/* Toggle Switch */
.cookie-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 24px;
    flex-shrink: 0;
}

.cookie-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.cookie-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.cookie-slider:before {
    position: absolute;
    content: "";
    height: 18px;
    width: 18px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .cookie-slider {
    background-color: var(--primary-color);
}

input:disabled + .cookie-slider {
    opacity: 0.5;
    cursor: not-allowed;
}

input:checked + .cookie-slider:before {
    transform: translateX(26px);
}

.rtl .cookie-slider:before {
    left: auto;
    right: 3px;
}

.rtl input:checked + .cookie-slider:before {
    transform: translateX(-26px);
}

.cookie-modal-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e2e8f0;
    text-align: right;
}

.rtl .cookie-modal-footer {
    text-align: left;
}

/* Dark mode styles */
.dark .cookie-banner {
    background-color: var(--secondary-color);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.dark .cookie-content h3 {
    color: white;
}

.dark .cookie-content p {
    color: #e2e8f0;
}

.dark .cookie-button.decline {
    background-color: #2d3748;
    color: white;
    border-color: #4a5568;
}

.dark .cookie-button.decline:hover {
    background-color: #1a202c;
}

.dark .cookie-button.settings {
    color: #e2e8f0;
}

.dark .cookie-modal-content {
    background-color: var(--secondary-color);
}

.dark .cookie-modal-header {
    border-color: #4a5568;
}

.dark .cookie-modal-header h3 {
    color: white;
}

.dark .cookie-option-text h4 {
    color: white;
}

.dark .cookie-option-text p {
    color: #e2e8f0;
}

.dark .cookie-modal-footer {
    border-color: #4a5568;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .cookie-container {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .cookie-buttons {
        width: 100%;
        justify-content: center;
    }
    
    .cookie-button {
        flex: 1;
        text-align: center;
    }
    
    .cookie-option {
        flex-direction: row;
    }
}

    </style>

    @stack('styles')
</head>
<body class="antialiased">
    <!-- Cookie Consent Banner -->
<div id="cookie-consent-banner" class="cookie-banner" style="display: none;">
    <div class="cookie-container {{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
        <div class="cookie-content">
            <h3>{{ __('messages.Cookie Consent') }}</h3>
            <p>{{ __('messages.cookie_message') }}</p>
        </div>
        <div class="cookie-buttons">
            <button id="cookie-accept" class="cookie-button accept">{{ __('messages.Accept All') }}</button>
            <button id="cookie-decline" class="cookie-button decline">{{ __('messages.Decline') }}</button>
            <button id="cookie-settings" class="cookie-button settings">{{ __('messages.Cookie Settings') }}</button>
        </div>
    </div>
</div>

<!-- Cookie Settings Modal -->
<div id="cookie-settings-modal" class="cookie-modal" style="display: none;">
    <div class="cookie-modal-content {{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
        <div class="cookie-modal-header">
            <h3>{{ __('messages.Cookie Settings') }}</h3>
            <button id="cookie-modal-close" class="cookie-modal-close">&times;</button>
        </div>
        <div class="cookie-modal-body">
            <p>{{ __('messages.cookie_settings_intro') }}</p>
            
            <div class="cookie-option">
                <label class="cookie-switch">
                    <input type="checkbox" id="necessary-cookies" checked disabled>
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-option-text">
                    <h4>{{ __('messages.Necessary Cookies') }}</h4>
                    <p>{{ __('messages.necessary_cookies_description') }}</p>
                </div>
            </div>
            
            <div class="cookie-option">
                <label class="cookie-switch">
                    <input type="checkbox" id="analytics-cookies">
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-option-text">
                    <h4>{{ __('messages.Analytics Cookies') }}</h4>
                    <p>{{ __('messages.analytics_cookies_description') }}</p>
                </div>
            </div>
            
            <div class="cookie-option">
                <label class="cookie-switch">
                    <input type="checkbox" id="marketing-cookies">
                    <span class="cookie-slider"></span>
                </label>
                <div class="cookie-option-text">
                    <h4>{{ __('messages.Marketing Cookies') }}</h4>
                    <p>{{ __('messages.marketing_cookies_description') }}</p>
                </div>
            </div>
        </div>
        <div class="cookie-modal-footer">
            <button id="cookie-save-preferences" class="cookie-button accept">{{ __('messages.Save Preferences') }}</button>
        </div>
    </div>
</div>

<button id="scrollToTop" class="scroll-to-top">
    <i class="fas fa-arrow-up"></i>
</button>
    @auth
    <nav class="guest-navbar">
            <div class="navbar-container">
                <!-- Logo -->
                <a href="/" class="logo">{{ config('app.name') }}</a>
                
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-button">
                    <i class="fas fa-bars"></i>
                </button>

                <!-- Navigation Links -->
                <div class="nav-links">
            <div class="nav-links-container">
                <div class="main-nav-group">
               

                    <!-- Dashboard Link -->
                    <a href="{{ route('dashboard') }}" class="nav-link">{{ __('messages.Dashboard') }}</a>
                    
                    <!-- Admin Link -->
                    @if(auth()->user()->hasRole('admin'))
                        <a href="{{ route('admin') }}" class="nav-link">{{ __('messages.Admin') }}</a>
                    @endif
                    
                    <!-- Profile Dropdown -->
                    <div class="profile-dropdown">
                        <button class="profile-button nav-link">
                            {{ Auth::user()->name }}
                            <i class="fas fa-chevron-down {{ app()->getLocale() === 'ar' ? 'fa-flip-horizontal' : '' }} ml-2"></i>
                        </button>
                        <div class="profile-dropdown-content">
    <a href="{{ route('profile.edit') }}" class="dropdown-item">
        <span class="dropdown-item-content">
            <i class="fas fa-user"></i>
            <span>{{ __('messages.Profile') }}</span>
        </span>
    </a>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="dropdown-item">
            <span class="dropdown-item-content">
                <i class="fas fa-sign-out-alt"></i>
                <span>{{ __('messages.Log Out') }}</span>
            </span>
        </button>
    </form>
</div>
                    </div>
                    <div class="language-switcher">
                    <button class="language-button">
                        <i class="fas fa-globe"></i>
                        <span>{{ strtoupper(Session::get('locale', 'en')) }}</span>
                    </button>
                    <div class="language-dropdown">
                        @php($languages = ['en' => 'English', 'fr' => 'Français', 'ar' => 'العربية'])
                        @foreach($languages as $code => $name)
                            <a href="{{ route('change.lang', ['lang' => $code]) }}" 
                               class="language-option {{ Session::get('locale') === $code ? 'active' : '' }}">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                       </div>
                    </div>
                </div>
            </div>
        </nav>

        @if(request()->routeIs('dashboard') || request()->routeIs('profile.*'))
            @if(isset($header))
                <header class="bg-white dark:bg-gray-800 shadow mt-16">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif
            <main class="py-12">
                {{ $slot ?? '' }}
                @yield('content')
            </main>

        <footer class="footer">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>{{ config('app.name') }}<br>YOUR BEST COMPANY</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4>Product</h4>
                    <ul class="footer-links">
                        <li><a href="#download">Download</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#features">Features</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Guidelines</a></li>
                    </ul>
                </div>
            </div>
        </footer>
        @else
            <main class="mt-16">
                @yield('content')
            </main>
              <footer class="footer">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>{{ config('app.name') }}<br>YOUR BEST COMPANY</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4>Product</h4>
                    <ul class="footer-links">
                        <li><a href="#download">Download</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#features">Features</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Guidelines</a></li>
                    </ul>
                </div>
            </div>
        </footer>
        @endif
    @else
    <nav class="guest-navbar">
    <div class="navbar-container">
        <a href="/" class="logo">{{ config('app.name') }}</a>
        
        <button class="mobile-menu-button">
            <i class="fas fa-bars"></i>
        </button>

        <div class="nav-links">
            <div class="nav-links-container">
                <div class="main-nav-group">
            
                    <a href="#features" class="nav-link">{{ __('messages.Features') }}</a>
                    <a href="#download" class="nav-link">{{ __('messages.Download') }}</a>
                    <a href="#pricing" class="nav-link">{{ __('messages.Pricing') }}</a>
                    <a href="#" class="nav-link">{{ __('messages.Support') }}</a>
                    <a href="{{ route('login') }}" class="nav-link">{{ __('messages.Login') }}</a>
                    <a href="{{ route('register') }}" class="nav-link">{{ __('messages.Sign Up') }}</a>
                    <div class="language-switcher">
                    <button class="language-button">
                        <i class="fas fa-globe"></i>
                        <span>{{ strtoupper(Session::get('locale', 'en')) }}</span>
                    </button>
                    <div class="language-dropdown">
                        @php($languages = ['en' => 'English', 'fr' => 'Français', 'ar' => 'العربية'])
                        @foreach($languages as $code => $name)
                            <a href="{{ route('change.lang', ['lang' => $code]) }}" 
                               class="language-option {{ Session::get('locale') === $code ? 'active' : '' }}">
                                {{ $name }}
                            </a>
                        @endforeach
                    </div>
                </div>
                </div>

            
            </div>
        </div>
    </div>
</nav>


        <main>
            @yield('content')
        </main>

        <footer class="footer">
            <div class="footer-grid">
                <div class="footer-brand">
                    <h3>{{ config('app.name') }}<br>YOUR BEST COMPANY</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="footer-column">
                    <h4>Product</h4>
                    <ul class="footer-links">
                        <li><a href="#download">Download</a></li>
                        <li><a href="#pricing">Pricing</a></li>
                        <li><a href="#features">Features</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Company</h4>
                    <ul class="footer-links">
                        <li><a href="#">About</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="#">Blog</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Resources</h4>
                    <ul class="footer-links">
                        <li><a href="#">Support</a></li>
                        <li><a href="#">Documentation</a></li>
                        <li><a href="#">Security</a></li>
                    </ul>
                </div>

                <div class="footer-column">
                    <h4>Legal</h4>
                    <ul class="footer-links">
                        <li><a href="#">Terms</a></li>
                        <li><a href="#">Privacy</a></li>
                        <li><a href="#">Guidelines</a></li>
                    </ul>
                </div>
            </div>
        </footer>
    @endauth

    <script>
        // Mobile Menu Toggle
        document.querySelector('.mobile-menu-button')?.addEventListener('click', function() {
            document.querySelector('.nav-links').classList.toggle('active');
        });

        // Language Switcher
        document.querySelector('.language-button')?.addEventListener('click', function(e) {
            e.stopPropagation();
            document.querySelector('.language-dropdown').classList.toggle('active');
        });

        // Profile Dropdown
        document.querySelector('.profile-button')?.addEventListener('click', function(e) {
            e.stopPropagation();
            this.closest('.profile-dropdown').classList.toggle('active');
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const languageSwitcher = document.querySelector('.language-switcher');
            const profileDropdown = document.querySelector('.profile-dropdown');
            
            if (languageSwitcher && !languageSwitcher.contains(event.target)) {
                languageSwitcher.querySelector('.language-dropdown')?.classList.remove('active');
            }

            if (profileDropdown && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
        });

        // Language Selection
// Language Selection (remove the existing code and replace with this)
document.querySelectorAll('.language-option').forEach(option => {
    option.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = this.href;
    });
});

        // Smooth Scroll
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const targetId = this.getAttribute('href').substring(1);
                const targetElement = document.getElementById(targetId);
                
                if (targetElement) {
                    const headerOffset = 80;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            });
        });


    </script>
<script>
    // Cookie Consent Script
    document.addEventListener('DOMContentLoaded', function() {
        const cookieBanner = document.getElementById('cookie-consent-banner');
        const cookieModal = document.getElementById('cookie-settings-modal');
        const acceptBtn = document.getElementById('cookie-accept');
        const declineBtn = document.getElementById('cookie-decline');
        const settingsBtn = document.getElementById('cookie-settings');
        const modalCloseBtn = document.getElementById('cookie-modal-close');
        const savePreferencesBtn = document.getElementById('cookie-save-preferences');
        const analyticsCookiesToggle = document.getElementById('analytics-cookies');
        const marketingCookiesToggle = document.getElementById('marketing-cookies');

        // Check if cookies are already set
        function checkCookieConsent() {
            if (getCookie('cookie-consent') === null) {
                // Show banner if consent not given yet
                cookieBanner.style.display = 'block';
            } else {
                // Apply saved preferences
                const preferences = JSON.parse(getCookie('cookie-preferences') || '{"necessary":true}');
                analyticsCookiesToggle.checked = preferences.analytics || false;
                marketingCookiesToggle.checked = preferences.marketing || false;
            }
        }

        // Get cookie by name
        function getCookie(name) {
            const value = `; ${document.cookie}`;
            const parts = value.split(`; ${name}=`);
            if (parts.length === 2) return parts.pop().split(';').shift();
            return null;
        }

        // Set cookie
        function setCookie(name, value, days) {
            let expires = "";
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = "; expires=" + date.toUTCString();
            }
            document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Lax";
        }

        // Accept all cookies
        function acceptAllCookies() {
            const preferences = {
                necessary: true,
                analytics: true,
                marketing: true
            };
            
            setCookie('cookie-consent', 'true', 365);
            setCookie('cookie-preferences', JSON.stringify(preferences), 365);
            cookieBanner.style.display = 'none';
            
            // Update toggles in modal
            analyticsCookiesToggle.checked = true;
            marketingCookiesToggle.checked = true;
            
            // Enable analytics and marketing scripts here
            enableAnalytics();
            enableMarketing();
        }

        // Decline non-essential cookies
        function declineCookies() {
            const preferences = {
                necessary: true,
                analytics: false,
                marketing: false
            };
            
            setCookie('cookie-consent', 'false', 365);
            setCookie('cookie-preferences', JSON.stringify(preferences), 365);
            cookieBanner.style.display = 'none';
            
            // Update toggles in modal
            analyticsCookiesToggle.checked = false;
            marketingCookiesToggle.checked = false;
        }

        // Save user preferences
        function savePreferences() {
            const preferences = {
                necessary: true,
                analytics: analyticsCookiesToggle.checked,
                marketing: marketingCookiesToggle.checked
            };
            
            setCookie('cookie-consent', 'custom', 365);
            setCookie('cookie-preferences', JSON.stringify(preferences), 365);
            cookieModal.style.display = 'none';
            cookieBanner.style.display = 'none';
            
            // Enable/disable scripts based on preferences
            if (preferences.analytics) {
                enableAnalytics();
            }
            
            if (preferences.marketing) {
                enableMarketing();
            }
        }

        // Functions to enable specific cookie categories
        function enableAnalytics() {
            // Add your analytics initialization code here
            console.log('Analytics cookies enabled');
        }

        function enableMarketing() {
            // Add your marketing cookies initialization code here
            console.log('Marketing cookies enabled');
        }

        // Event listeners
        acceptBtn.addEventListener('click', acceptAllCookies);
        declineBtn.addEventListener('click', declineCookies);
        settingsBtn.addEventListener('click', function() {
            cookieModal.style.display = 'flex';
        });
        modalCloseBtn.addEventListener('click', function() {
            cookieModal.style.display = 'none';
        });
        savePreferencesBtn.addEventListener('click', savePreferences);

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === cookieModal) {
                cookieModal.style.display = 'none';
            }
        });

        // Initialize
        checkCookieConsent();
        
        // Apply saved cookie preferences on page load
        const preferences = JSON.parse(getCookie('cookie-preferences') || '{"necessary":true}');
        if (preferences.analytics) {
            enableAnalytics();
        }
        if (preferences.marketing) {
            enableMarketing();
        }
    });
</script>

    @stack('scripts')
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

</body>
</html>
