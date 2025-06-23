<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Landing Page</title>
    
    <!-- Remix Icons -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.4.0/fonts/remixicon.css" rel="stylesheet" />
    
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/sass/app.scss','resources/js/app.js'])
    
    <style>
        :root {
            --primary-color: #014421;
            --secondary-color: #D0D8C3;
            --accent-color: #2d6a3e;
            --light-green: #e8f5e8;
            --dark-green: #012d17;
        }
        
        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f8f9fa;
        }
        
        .navbar {
            background: rgba(208, 216, 195, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(1, 68, 33, 0.1);
        }
        
        .navbar-brand {
            color: var(--primary-color) !important;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-nav .nav-link {
            color: var(--primary-color) !important;
            font-weight: 600;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }
        
        .navbar-nav .nav-link:hover {
            color: var(--accent-color) !important;
            transform: translateY(-1px);
        }
        
        .navbar-nav .nav-link.active {
            color: var(--accent-color) !important;
            font-weight: 700;
        }
        
        .nav__logo {
            height: 40px;
            width: auto;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(1, 68, 33, 0.3);
            color: white;
            background: linear-gradient(135deg, var(--accent-color), var(--primary-color));
        }
        
        .main-content {
            min-height: calc(100vh - 200px);
            padding-top: 100px;
        }
        
        .content-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(1, 68, 33, 0.1);
            border: 2px solid var(--secondary-color);
            transition: all 0.3s ease;
        }
        
        .content-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(1, 68, 33, 0.15);
        }
        
        .page-header {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .page-subheader {
            color: var(--dark-green);
        }
        
        .footer-section {
            background: var(--primary-color);
            color: white;
            padding: 40px 0 20px;
            margin-top: auto;
        }
        
        .footer-text {
            color: var(--secondary-color);
            opacity: 0.8;
        }
        
        .navbar-toggler {
            border-color: var(--primary-color);
        }
        
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(1, 68, 33, 0.25);
        }
        
        @media (max-width: 768px) {
            .main-content {
                padding-top: 80px;
            }
            
            .navbar-nav {
                text-align: center;
                padding: 1rem 0;
            }
            
            .navbar-nav .nav-link {
                margin: 0.25rem 0;
            }
            
            .btn-custom {
                margin-top: 1rem;
                width: 100%;
            }
        }
    </style>
    
    
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                {{-- <img src="{{ asset('img/logos.png') }}" alt="logo" class="nav__logo"/> --}}
                Navbar Brand
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
           <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item">
                        <a href="/" class="btn btn-custom me-2">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="/login" class="btn btn-custom me-2">Login</a>
                    </li>
                    <li class="nav-item">
                        <a href="/enroll" class="btn btn-custom">Enroll</a>
                    </li>
                </ul>
            </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-section mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 class="text-white fw-bold mb-3">Footer</h5>
                    <p class="footer-text">
                        Footer Message
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="social-links">
                        <a href="#" class="text-decoration-none me-3" aria-label="Facebook">
                            <i class="ri-facebook-fill"></i>
                        </a>
                        <a href="#" class="text-decoration-none me-3" aria-label="Instagram">
                            <i class="ri-instagram-line"></i>
                        </a>
                        <a href="#" class="text-decoration-none" aria-label="Twitter">
                            <i class="ri-twitter-fill"></i>
                        </a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--secondary-color); opacity: 0.3;">
            <div class="row">
                <div class="col-12 text-center">
                    <p class="footer-text mb-0">
                        © {{ date('Y') }} Nicolites School Management System. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </footer>

   
    
    <!-- Custom Scripts -->
    <script>
        // Smooth scrolling for Nav Links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Navbar background on scroll
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(208, 216, 195, 0.98)';
                navbar.style.boxShadow = '0 2px 20px rgba(1, 68, 33, 0.2)';
            } else {
                navbar.style.background = 'rgba(208, 216, 195, 0.95)';
                navbar.style.boxShadow = '0 2px 20px rgba(1, 68, 33, 0.1)';
            }
        });

        // Add animation on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe content cards for animation
        document.querySelectorAll('.content-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
