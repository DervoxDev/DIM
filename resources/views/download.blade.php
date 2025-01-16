<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Télécharger {{ config('app.name') }}</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', sans-serif;
            background-color: #404EED;
            color: #fff;
            line-height: 1.6;
        }

        .navbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            padding: 24px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 100;
            background: blue;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            font-size: 25px;
            gap: 70px;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        .nav-link:hover {
            color: #5865F2;
        }

        .button {
            display: inline-flex;
            align-items: center;
            padding: 16px 32px;
            border-radius: 28px;
            font-size: 20px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
        }


        .white-button {
            background: white;
            color: #23272A;
        }

        .white-button:hover {
            color: #5865F2;
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 120px 20px 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 80px;
            padding-top: 40px;
        }

        .header h1 {
            font-size: 56px;
            margin-bottom: 24px;
            font-weight: 800;
        }

        .header p {
            font-size: 20px;
            color: rgba(255,255,255,0.9);
            max-width: 700px;
            margin: 0 auto;
        }

        .download-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin-bottom: 60px;
            padding: 0 20px;
        }

        .download-card {
            background: white;
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
            color: #23272A;
        }

        .download-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        .os-icon {
            font-size: 64px;
            margin-bottom: 24px;
        }

        .download-card h2 {
            font-size: 24px;
            margin-bottom: 16px;
            color: #23272A;
        }

        .download-card p {
            color: #4F5660;
            margin-bottom: 24px;
            font-size: 16px;
        }

        .download-button {
            display: inline-block;
            padding: 12px 32px;
            background-color: #5865F2;
            color: white;
            text-decoration: none;
            border-radius: 28px;
            font-weight: 500;
            transition: all 0.3s;
            margin: 8px 0;
        }

        .download-button:hover {
            background-color: #4752C4;
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(88,101,242,0.3);
        }

        .version-info {
            font-size: 14px;
            color: #4F5660;
            margin-top: 16px;
        }

        .linux-downloads {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        @media (max-width: 768px) {
            .navbar {
                padding: 16px 20px;
            }

            .nav-links {
                gap: 20px;
            }

            .header h1 {
                font-size: 40px;
            }

            .download-grid {
                grid-template-columns: 1fr;
                padding: 0;
            }
        }
         /* Footer */
         .footer {
            background: #23272A;
            padding: 80px 40px 40px;
            color: white;
        }

        .footer-grid {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 2fr repeat(4, 1fr);
            gap: 40px;
            padding-bottom: 40px;
            border-bottom: 1px solid #5865F2;
            margin-bottom: 32px;
        }

        .footer-brand h3 {
            font-size: 32px;
            margin-bottom: 24px;
            color: #5865F2;
        }

        .social-links {
            display: flex;
            gap: 24px;
            margin-top: 30px;
            font-size: 20px;
        }

        .social-links a {
            color: white;
            text-decoration: none;
        }

        .footer-column h4 {
            color: #5865F2;
            margin-bottom: 16px;
            font-size: 40px;
        }

        .footer-links {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: white;
            text-decoration: none;
            font-size: 20px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        .footer-bottom {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 32px;
        }

        .footer-bottom .logo {
            font-size: 20px;
        }

        .signup-button {
            background: #5865F2;
            color: white;
            padding: 12px 24px;
            border-radius: 40px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.2s;
        }

        .signup-button:hover {
            background: #4752C4;
        }

        @media (max-width: 768px) {
            .footer-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-bottom {
                flex-direction: column;
                gap: 24px;
                text-align: center;
            }
        }
        .download-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #fff;
            min-width: 200px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 8px 0;
            z-index: 1000;
            top: 100%;
            left: 50%;
            transform: translateX(-50%);
            margin-top: 8px;
        }

        .download-dropdown:hover .dropdown-content {
            display: block;
        }

        .dropdown-content a {
            color: #23272A;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.2s;
        }

        .dropdown-content a:hover {
            background-color: #f6f6f6;
        }

        .download-dropdown::after {
            content: '▼';
            margin-left: 8px;
            font-size: 12px;
        }
        .modal {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: transparent;
        z-index: 1000;
        animation: slideUp 0.3s ease-out;
    }

    .modal-content {
        position: relative;
        background: #36393f;
        max-width: 480px;
        margin: 0 24px 24px;
        padding: 16px;
        border-radius: 5px;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.24);
    }

    .close-modal {
        position: absolute;
        right: 16px;
        top: 16px;
        font-size: 20px;
        color: #dcddde;
        cursor: pointer;
        background: none;
        border: none;
        padding: 4px;
        border-radius: 3px;
        transition: background-color 0.2s;
    }

    .close-modal:hover {
        background-color: rgba(220, 221, 222, 0.1);
    }

    .modal-title {
        font-size: 20px;
        font-weight: 600;
        color: white;
        margin-bottom: 8px;
        padding-right: 24px;
    }

    .modal-text {
        color: #b9bbbe;
        font-size: 14px;
        line-height: 1.4;
        margin-bottom: 16px;
    }

    .modal-text ul {
        margin: 8px 0;
        padding-left: 20px;
    }

    .modal-text li {
        margin-bottom: 4px;
    }

    .modal-buttons {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        margin-top: 16px;
    }

    .modal-button {
        padding: 8px 16px;
        border-radius: 3px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: background-color 0.2s;
        border: none;
    }

    .modal-button.primary {
        background-color: #5865f2;
        color: white;
    }

    .modal-button.primary:hover {
        background-color: #4752c4;
    }

    .modal-button.secondary {
        background-color: transparent;
        color: #fff;
    }

    .modal-button.secondary:hover {
        background-color: rgba(220, 221, 222, 0.1);
    }

    .modal.active {
        display: block;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
        }
        to {
            transform: translateY(0);
        }
    }

    @media (min-width: 768px) {
        .modal-content {
            margin: 0 auto 24px;
        }
    }
    </style>
 <script>
  
    function openModal(type) {
        document.getElementById(type + 'Modal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(type) {
        document.getElementById(type + 'Modal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    // Fermer le modal si on clique en dehors
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
</script>
   
</head>


<body>
<nav class="navbar">
        <a href="/" class="logo">{{ config('app.name') }}</a>
        <div class="nav-links">
        <a href="{{ route('download') }}" class="nav-link" data-lang-key="nav.download">Télécharger</a>
            <a href="#" class="nav-link">Nitro</a>
            <a href="#" class="nav-link">Découvrir</a>
            <a href="#" class="nav-link">Sécurité</a>
            <a href="#" class="nav-link">Support</a>
            @auth
                <a href="{{ url('/dashboard') }}" class="button white-button">Ouvrir {{ config('app.name') }}</a>
            @else
                <a href="{{ route('login') }}" class="button white-button">Se connecter</a>
            @endauth
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Télécharger {{ config('app.name') }}</h1>
            <p>Choisissez votre plateforme et commencez à discuter</p>
        </div>

        <div class="download-grid">
            <!-- Windows -->
            <div class="download-card">
                <div class="os-icon">🪟</div>
                <h2>Windows</h2>
                <p>Windows 8.1 ou supérieur</p>
                <a href="{{ route('download.os', 'windows') }}" class="download-button">
                    Télécharger pour Windows
                </a>
                <div class="version-info">
                    Version 1.0.0 (64-bit) • 68.5 MB
                </div>
            </div>

            <!-- macOS -->
            <div class="download-card">
                <div class="os-icon">🍎</div>
                <h2>macOS</h2>
                <p>macOS 10.13 ou supérieur</p>
                <a href="{{ route('download.os', 'mac') }}" class="download-button">
                    Télécharger pour macOS
                </a>
                <div class="version-info">
                    Version 1.0.0 • 72.3 MB
                </div>
            </div>

            <!-- Linux -->
            <div class="download-card">
                <div class="os-icon">🐧</div>
                <h2>Linux</h2>
                <p>Ubuntu, Fedora ou autres</p>
                <div class="linux-downloads">
                    <a href="{{ route('download.os', ['os' => 'linux', 'type' => 'deb']) }}" class="download-button">
                        Télécharger .deb
                    </a>
                    <a href="{{ route('download.os', ['os' => 'linux', 'type' => 'rpm']) }}" class="download-button">
                        Télécharger .rpm
                    </a>
                    <a href="{{ route('download.os', ['os' => 'linux', 'type' => 'appimage']) }}" class="download-button">
                        Télécharger .AppImage
                    </a>
                </div>
                <div class="version-info">
                    Version 1.0.0 • ~64 MB
                </div>
            </div>
        </div>
    </div>
    <footer class="footer">
        <div class="footer-grid">
            <div class="footer-brand">
                <h3>DERVOX<br>YOUR BEST COMPANY</h3>
                <div class="social-links">
                    <a href="#"><span>Twitter</span></a>
                    <a href="#"><span>Instagram</span></a>
                    <a href="#"><span>Facebook</span></a>
                    <a href="#"><span>YouTube</span></a>
                </div>
            </div>
            
            <div class="footer-column">
                <h4>Produit</h4>
                <ul class="footer-links">
                    <li><a href="#">Télécharger</a></li>
                    <li><a href="#">Nitro</a></li>
                    <li><a href="#">Statut</a></li>
                    <li><a href="#">Boutique</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Entreprise</h4>
                <ul class="footer-links">
                    <li><a href="#">À propos</a></li>
                    <li><a href="#">Emplois</a></li>
                    <li><a href="#">Marque</a></li>
                    <li><a href="#">Actualités</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Ressources</h4>
                <ul class="footer-links">
                    <li><a href="#">Université</a></li>
                    <li><a href="#">Support</a></li>
                    <li><a href="#">Sécurité</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Commentaires</a></li>
                    <li><a href="#">Développeurs</a></li>
                    <li><a href="#">StreamKit</a></li>
                </ul>
            </div>

            <div class="footer-column">
                <h4>Règles</h4>
                <ul class="footer-links">
                   <li><a href="#" onclick="openModal('terms')">Conditions</a></li>
                   <li><a href="#" onclick="openModal('privacy')">Confidentialité</a></li>
                   <li><a href="#"  onclick="openModal('cookies')">Paramètres des cookies</a></li>
                   <li><a href="#"  onclick="openModal('guidelines')">Directives</a></li>
                   <li><a href="#" onclick="openModal('acknowledgments')">Remerciements</a></li>
                   <li><a href="#" onclick="openModal('licenses')">Licences</a></li>
                   <li><a href="#" onclick="openModal('moderation')">Modération</a></li>
                </ul>
            </div>
        </div>
        <!-- Modal Conditions -->
        <div id="termsModal" class="modal">
            <div class="modal-content">
                <button class="close-modal" onclick="closeModal('terms')">&times;</button>
                <h2 class="modal-title">Conditions d'utilisation</h2>
                <div class="modal-text">
                    <p>En utilisant nos services, vous acceptez nos conditions d'utilisation :</p>
                    <ul>
                        <li>Respecter les droits des autres utilisateurs</li>
                        <li>Ne pas partager de contenu inapproprié</li>
                        <li>Ne pas utiliser le service à des fins malveillantes</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Modal Confidentialité -->
        <div id="privacyModal" class="modal">
            <div class="modal-content">
                <button class="close-modal" onclick="closeModal('privacy')">&times;</button>
                <h2 class="modal-title">Politique de confidentialité</h2>
                <div class="modal-text">
                    <p>Nous prenons votre vie privée au sérieux :</p>
                    <ul>
                        <li>Vos données sont cryptées</li>
                        <li>Nous ne vendons pas vos informations</li>
                        <li>Vous contrôlez vos données</li>
                    </ul>
                </div>
            </div>
        </div>

   <!-- Modal Cookies -->
<div id="cookiesModal" class="modal">
    <div class="modal-content">
        <button class="close-modal" onclick="closeModal('cookies')">&times;</button>
        <h2 class="modal-title">Paramètres des cookies</h2>
        <div class="modal-text">
            <p>Nous utilisons des cookies pour personnaliser le contenu, fournir des fonctionnalités de médias sociaux et analyser notre trafic. Nous partageons également des informations sur votre utilisation de notre site avec nos partenaires de médias sociaux, de publicité et d'analyse.</p>
            <p>Vous pouvez choisir les cookies que vous acceptez :</p>
            <div class="cookie-options">
                <label class="cookie-option">
                    <input type="checkbox" checked disabled>
                    <span>Strictement nécessaires</span>
                </label>
                <label class="cookie-option">
                    <input type="checkbox" checked>
                    <span>Fonctionnels</span>
                </label>
                <label class="cookie-option">
                    <input type="checkbox" checked>
                    <span>Performance</span>
                </label>
            </div>
        </div>
        <div class="modal-buttons">
            <button class="modal-button secondary" onclick="closeModal('cookies')">Personnaliser</button>
            <button class="modal-button primary" onclick="acceptAllCookies()">Accepter tout</button>
        </div>
    </div>
</div>


    <!-- Modal Directives -->
    <div id="guidelinesModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('guidelines')">&times;</button>
            <h2 class="modal-title">Directives de la communauté</h2>
            <div class="modal-text">
                <p>Pour maintenir une communauté saine :</p>
                <ul>
                    <li>Soyez respectueux envers les autres</li>
                    <li>Évitez le contenu offensant</li>
                    <li>Suivez les règles du serveur</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Remerciements -->
    <div id="acknowledgementsModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('acknowledgments')">&times;</button>
            <h2 class="modal-title">Remerciements</h2>
            <div class="modal-text">
                <p>Nous remercions :</p>
                <ul>
                    <li>Notre communauté d'utilisateurs</li>
                    <li>Nos partenaires</li>
                    <li>Les contributeurs open source</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Licences -->
    <div id="licensesModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('licenses')">&times;</button>
            <h2 class="modal-title">Licences</h2>
            <div class="modal-text">
                <p>Informations sur les licences :</p>
                <ul>
                    <li>Licence du logiciel</li>
                    <li>Licences tierces</li>
                    <li>Droits d'utilisation</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal Modération -->
    <div id="moderationModal" class="modal">
        <div class="modal-content">
            <button class="close-modal" onclick="closeModal('moderation')">&times;</button>
            <h2 class="modal-title">Politique de modération</h2>
            <div class="modal-text">
                <p>Notre approche de la modération :</p>
                <ul>
                    <li>Modération équitable et transparente</li>
                    <li>Protection contre le harcèlement</li>
                    <li>Système d'appel des décisions</li>
                </ul>
            </div>
        </div>
    </div>

        <div class="footer-bottom">
            <a href="/" class="logo">{{ config('app.name') }}</a>
            @guest
                <a href="{{ route('register') }}" class="signup-button">S'inscrire</a>
            @endguest
        </div>
    </footer>
</body>

<script>
  
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
</script>
</html>
</head>