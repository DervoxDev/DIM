<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
      dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}" 
      class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title', 'Dervox')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jquery-images-compare@0.2.5/src/assets/css/images-compare.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <link href="https://unpkg.com/ionicons@4.5.10-0/dist/css/ionicons.min.css" rel="stylesheet">
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
    --primary-color: #05346b;    /* Deep blue instead of purple */
    --secondary-color: #23272A;  /* Keeping dark gray */
    --accent-color: #0056b3;     /* Brighter blue instead of purple accent */
    --text-color: #4F5660;       /* Keeping text gray */
    --light-color: #FFFFFF;      /* Keeping white */
    --background-color: #F6F6F6; /* Keeping light background */
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
        .logo-image {
    width: 55px;
    height: auto;
    margin-right: 10px;
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
            color: #074b98;
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
            color: #074b98;
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
    </style>

    @stack('styles')
</head>
<body class="antialiased">
<div id="loader">
    <div class="loader-content">
        <img src="{{ Vite::asset('resources/images/logo_full.png') }}" alt="Loading..." class="loader-logo">
    </div>
</div>
<nav class="guest-navbar">
    <div class="navbar-container">
    <a href="/" class="logo">
            <img src="{{ Vite::asset('resources/images/logo.png') }}" alt="Dervox" class="logo-image">
        </a>        
        <button class="mobile-menu-button">
            <i class="fas fa-bars"></i>
        </button>

        <div class="nav-links">
            <div class="nav-links-container">
                <div class="main-nav-group">
            
                <a href="{{ route('dervox') }}" class="nav-link">Home</a>
                <a href="{{ route('about') }}" class="nav-link">About</a>
                <a href="{{ route('solutions') }}" class="nav-link">Solutions</a>
                <a href="{{ route('services') }}" class="nav-link">Services</a>
                <a href="#contact" class="nav-link">Contact us</a>
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
        @stack('scripts')
    <!-- Scripts -->
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
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-images-compare@0.2.5/build/jquery.images-compare.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
</body>
</html>
