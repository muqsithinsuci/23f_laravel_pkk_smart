<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Smart PKK - Sistem Manajemen PKK Modern')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logopkk.png') }}">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        }
        
        .hover-lift {
            transition: all 0.3s ease;
        }
        
        .hover-lift:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1), 0 10px 10px -5px rgba(59, 130, 246, 0.04);
        }
        
        .fade-in {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.6s ease forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
    </style>
    
    @yield('styles')
</head>
<body class="bg-white text-gray-800 font-sans">
    <!-- Header/Navbar -->
    <header class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-50">
        <nav class="container mx-auto px-4 lg:px-8">
            <div class="flex items-center justify-between h-16 lg:h-20">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/logopkk.png') }}" alt="Logo PKK" class="h-10 w-10 lg:h-12 lg:w-12">
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden lg:flex items-center space-x-8">
                    <a href="#home" class="text-gray-600 hover:text-primary-600 font-medium transition-colors">Beranda</a>
                    <a href="#features" class="text-gray-600 hover:text-primary-600 font-medium transition-colors">Fitur</a>
                    <a href="#about" class="text-gray-600 hover:text-primary-600 font-medium transition-colors">Tentang</a>
                    <a href="#contact" class="text-gray-600 hover:text-primary-600 font-medium transition-colors">Kontak</a>
                </div>
                
                <!-- CTA Button -->
                <div class="flex items-center space-x-4">
                    <a href="/admin" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-2 lg:px-8 lg:py-3 rounded-lg font-semibold transition-all hover-lift">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Daftar Sekarang
                    </a>
                    
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="lg:hidden text-gray-600 hover:text-primary-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <div id="mobile-menu" class="lg:hidden hidden pb-4">
                <div class="flex flex-col space-y-3">
                    <a href="#home" class="text-gray-600 hover:text-primary-600 font-medium py-2 transition-colors">Beranda</a>
                    <a href="#features" class="text-gray-600 hover:text-primary-600 font-medium py-2 transition-colors">Fitur</a>
                    <a href="#about" class="text-gray-600 hover:text-primary-600 font-medium py-2 transition-colors">Tentang</a>
                    <a href="#contact" class="text-gray-600 hover:text-primary-600 font-medium py-2 transition-colors">Kontak</a>
                </div>
            </div>
        </nav>
    </header>
    
    <!-- Main Content -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer class="bg-gray-50 border-t border-gray-100">
        <div class="container mx-auto px-4 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 lg:col-span-2">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ asset('images/logopkk.png') }}" alt="Logo PKK" class="h-12 w-12">

                    </div>
                    <p class="text-gray-600 mb-4 max-w-md">
                        Solusi digital terdepan untuk mengelola data keluarga, agenda kegiatan, dan kunjungan rumah 
                        PKK dengan mudah dan efisien.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-primary-600 transition-colors">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary-600 transition-colors">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary-600 transition-colors">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-primary-600 transition-colors">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Menu Cepat</h4>
                    <ul class="space-y-2">
                        <li><a href="#home" class="text-gray-600 hover:text-primary-600 transition-colors">Beranda</a></li>
                        <li><a href="#features" class="text-gray-600 hover:text-primary-600 transition-colors">Fitur</a></li>
                        <li><a href="#about" class="text-gray-600 hover:text-primary-600 transition-colors">Tentang Kami</a></li>
                        <li><a href="#contact" class="text-gray-600 hover:text-primary-600 transition-colors">Kontak</a></li>
                        <li><a href="/admin" class="text-gray-600 hover:text-primary-600 transition-colors">Dashboard</a></li>
                    </ul>
                </div>
                
                <!-- Contact Info -->
                <div>
                    <h4 class="text-lg font-semibold text-gray-800 mb-4">Kontak</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-map-marker-alt text-primary-600"></i>
                            <span class="text-gray-600">Jl. PKK No. 123, Jakarta</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-phone text-primary-600"></i>
                            <span class="text-gray-600">+62 21 1234 5678</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-primary-600"></i>
                            <span class="text-gray-600">info@smartpkk.id</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-clock text-primary-600"></i>
                            <span class="text-gray-600">Senin - Jumat, 08:00 - 17:00</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Copyright -->
            <div class="border-t border-gray-200 mt-8 pt-8 text-center">
                <p class="text-gray-500">
                    &copy; {{ date('Y') }} Smart PKK. All rights reserved. 
                    <span class="mx-2">|</span>
                    <a href="#" class="hover:text-primary-600 transition-colors">Privacy Policy</a>
                    <span class="mx-2">|</span>
                    <a href="#" class="hover:text-primary-600 transition-colors">Terms of Service</a>
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
        
        // Add scroll effect to navbar
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 100) {
                header.classList.add('shadow-lg');
            } else {
                header.classList.remove('shadow-lg');
            }
        });
    </script>
    
    @yield('scripts')
</body>
</html>