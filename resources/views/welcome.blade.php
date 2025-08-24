@extends('layouts.app')

@section('title', 'Smart PKK - Sistem Manajemen PKK Modern')

@section('content')
<!-- Hero Section -->
<section id="home" class="min-h-screen flex items-center gradient-bg relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800"></div>
    <div class="absolute inset-0 bg-black opacity-10"></div>
    
    <!-- Background Pattern -->
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-white rounded-full mix-blend-multiply filter blur-xl animate-pulse"></div>
            <div class="absolute top-1/3 right-1/4 w-72 h-72 bg-blue-200 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-2000"></div>
            <div class="absolute bottom-1/4 left-1/3 w-72 h-72 bg-blue-100 rounded-full mix-blend-multiply filter blur-xl animate-pulse animation-delay-4000"></div>
        </div>
    </div>
    
    <div class="container mx-auto px-4 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <!-- Content -->
            <div class="text-white">
                <h1 class="text-4xl lg:text-6xl font-bold mb-6 fade-in">
                    Modernisasi 
                    <span class="text-blue-200">PKK</span> 
                    dengan Smart PKK
                </h1>
                <p class="text-xl lg:text-2xl mb-8 text-blue-100 fade-in stagger-1">
                    Sistem manajemen digital yang memudahkan pengelolaan data keluarga, 
                    agenda kegiatan, dan kunjungan rumah PKK.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 fade-in stagger-2">
                    <a href="/admin" class="bg-white text-primary-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-all hover-lift inline-flex items-center justify-center">
                        <i class="fas fa-rocket mr-3"></i>
                        Mulai Sekarang
                    </a>
                    <a href="#features" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-primary-600 transition-all inline-flex items-center justify-center">
                        <i class="fas fa-play mr-3"></i>
                        Lihat Fitur
                    </a>
                </div>
                
                <!-- Stats -->
                <div class="grid grid-cols-3 gap-6 mt-12 fade-in stagger-3">
                    <div class="text-center">
                        <div class="text-3xl lg:text-4xl font-bold text-white">1000+</div>
                        <div class="text-blue-200">Keluarga</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl lg:text-4xl font-bold text-white">50+</div>
                        <div class="text-blue-200">PKK Aktif</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl lg:text-4xl font-bold text-white">99%</div>
                        <div class="text-blue-200">Kepuasan</div>
                    </div>
                </div>
            </div>
            
            <!-- Illustration -->
            <div class="hidden lg:block fade-in stagger-4">
                <div class="relative">
                    <div class="bg-white bg-opacity-10 backdrop-blur-lg rounded-3xl p-8 border border-white border-opacity-20">
                        <div class="space-y-6">
                            <!-- Dashboard Preview -->
                            <div class="bg-white bg-opacity-20 rounded-xl p-4">
                                <div class="flex items-center space-x-3 mb-4">
                                    <div class="w-3 h-3 bg-red-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-yellow-400 rounded-full"></div>
                                    <div class="w-3 h-3 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="space-y-3">
                                    <div class="bg-white bg-opacity-30 rounded-lg h-4"></div>
                                    <div class="bg-white bg-opacity-20 rounded-lg h-4 w-3/4"></div>
                                    <div class="bg-white bg-opacity-25 rounded-lg h-4 w-1/2"></div>
                                </div>
                            </div>
                            
                            <!-- Feature Cards -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white bg-opacity-15 rounded-xl p-4 text-center">
                                    <i class="fas fa-users text-2xl text-white mb-2"></i>
                                    <div class="text-white font-semibold">Data Keluarga</div>
                                </div>
                                <div class="bg-white bg-opacity-15 rounded-xl p-4 text-center">
                                    <i class="fas fa-calendar text-2xl text-white mb-2"></i>
                                    <div class="text-white font-semibold">Agenda</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section id="features" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-5xl font-bold text-gray-800 mb-6">Fitur Unggulan</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Smart PKK menyediakan berbagai fitur canggih untuk memudahkan pengelolaan 
                dan administrasi PKK di era digital.
            </p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <!-- Feature 1 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift">
                <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-users text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Manajemen Data Keluarga</h3>
                <p class="text-gray-600 mb-4">
                    Kelola data keluarga secara digital dengan sistem yang terintegrasi. 
                    Mencakup data anggota, gizi balita, dan ibu hamil.
                </p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Data lengkap anggota keluarga</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Monitoring gizi balita</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Tracking ibu hamil</li>
                </ul>
            </div>
            
            <!-- Feature 2 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift">
                <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-calendar-alt text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Agenda Kegiatan</h3>
                <p class="text-gray-600 mb-4">
                    Rencanakan dan kelola agenda kegiatan PKK dengan mudah. 
                    Dari posyandu hingga gotong royong.
                </p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Penjadwalan otomatis</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Notifikasi reminder</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Laporan kegiatan</li>
                </ul>
            </div>
            
            <!-- Feature 3 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift">
                <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-home text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Kunjungan Rumah</h3>
                <p class="text-gray-600 mb-4">
                    Dokumentasi kunjungan rumah dengan checklist sanitasi 
                    dan follow-up yang terstruktur.
                </p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Checklist sanitasi</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Foto dokumentasi</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Sistem follow-up</li>
                </ul>
            </div>
            
            <!-- Feature 4 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift">
                <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-chart-line text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Dashboard Analytics</h3>
                <p class="text-gray-600 mb-4">
                    Pantau perkembangan PKK melalui dashboard yang informatif 
                    dengan berbagai metrik penting.
                </p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Statistik real-time</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Grafik interaktif</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Export laporan</li>
                </ul>
            </div>
            
            <!-- Feature 5 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift">
                <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-mobile-alt text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Mobile Friendly</h3>
                <p class="text-gray-600 mb-4">
                    Akses dari mana saja menggunakan smartphone atau tablet 
                    dengan interface yang responsif.
                </p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Responsive design</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Offline capability</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Touch optimized</li>
                </ul>
            </div>
            
            <!-- Feature 6 -->
            <div class="bg-white p-8 rounded-2xl shadow-lg hover-lift">
                <div class="w-16 h-16 bg-primary-100 rounded-xl flex items-center justify-center mb-6">
                    <i class="fas fa-shield-alt text-2xl text-primary-600"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">Keamanan Data</h3>
                <p class="text-gray-600 mb-4">
                    Data PKK Anda aman dengan enkripsi tingkat tinggi 
                    dan backup otomatis yang terjadwal.
                </p>
                <ul class="text-sm text-gray-500 space-y-2">
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Enkripsi end-to-end</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Backup otomatis</li>
                    <li><i class="fas fa-check text-green-500 mr-2"></i>Multi-level authentication</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-20 bg-white">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div>
                <h2 class="text-3xl lg:text-5xl font-bold text-gray-800 mb-6">
                    Mengapa Memilih Smart PKK?
                </h2>
                <p class="text-xl text-gray-600 mb-8">
                    Smart PKK hadir untuk menjawab tantangan pengelolaan PKK di era digital. 
                    Dengan teknologi terdepan, kami membuat administrasi PKK menjadi lebih mudah, 
                    efisien, dan terorganisir.
                </p>
                
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-lightbulb text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Mudah Digunakan</h4>
                            <p class="text-gray-600">Interface yang intuitif dan user-friendly, cocok untuk semua kalangan.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-cogs text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Fitur Lengkap</h4>
                            <p class="text-gray-600">Semua kebutuhan administrasi PKK dalam satu platform terpadu.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                            <i class="fas fa-headset text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="text-lg font-semibold text-gray-800 mb-2">Support 24/7</h4>
                            <p class="text-gray-600">Tim support yang siap membantu Anda kapan saja dibutuhkan.</p>
                        </div>
                    </div>
                </div>
                
                <div class="mt-8">
                    <a href="/admin" class="bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-lg font-semibold text-lg transition-all hover-lift inline-flex items-center">
                        <i class="fas fa-arrow-right mr-3"></i>
                        Coba Sekarang
                    </a>
                </div>
            </div>
            
            <!-- Image/Illustration -->
            <div class="order-first lg:order-last">
                <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-3xl p-8 lg:p-12">
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Card 1 -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover-lift">
                            <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-users text-primary-600"></i>
                            </div>
                            <h5 class="font-semibold text-gray-800 mb-2">1,234</h5>
                            <p class="text-sm text-gray-600">Total Keluarga</p>
                        </div>
                        
                        <!-- Card 2 -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover-lift mt-6">
                            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-calendar-check text-green-600"></i>
                            </div>
                            <h5 class="font-semibold text-gray-800 mb-2">89</h5>
                            <p class="text-sm text-gray-600">Kegiatan Bulan Ini</p>
                        </div>
                        
                        <!-- Card 3 -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover-lift -mt-6">
                            <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-home text-yellow-600"></i>
                            </div>
                            <h5 class="font-semibold text-gray-800 mb-2">156</h5>
                            <p class="text-sm text-gray-600">Kunjungan Rumah</p>
                        </div>
                        
                        <!-- Card 4 -->
                        <div class="bg-white rounded-2xl p-6 shadow-lg hover-lift">
                            <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center mb-4">
                                <i class="fas fa-chart-line text-red-600"></i>
                            </div>
                            <h5 class="font-semibold text-gray-800 mb-2">98%</h5>
                            <p class="text-sm text-gray-600">Efisiensi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 gradient-bg">
    <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-primary-800"></div>
    <div class="container mx-auto px-4 lg:px-8 relative">
        <div class="text-center text-white">
            <h2 class="text-3xl lg:text-5xl font-bold mb-6">
                Siap Memulai Digitalisasi PKK?
            </h2>
            <p class="text-xl lg:text-2xl text-blue-100 mb-8 max-w-3xl mx-auto">
                Bergabunglah dengan ribuan PKK yang telah merasakan kemudahan 
                mengelola data dengan Smart PKK.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/admin" class="bg-white text-primary-600 px-8 py-4 rounded-lg font-semibold text-lg hover:bg-blue-50 transition-all hover-lift inline-flex items-center justify-center">
                    <i class="fas fa-rocket mr-3"></i>
                    Mulai Gratis Sekarang
                </a>
                <a href="#contact" class="border-2 border-white text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-white hover:text-primary-600 transition-all inline-flex items-center justify-center">
                    <i class="fas fa-phone mr-3"></i>
                    Hubungi Kami
                </a>
            </div>
            
            <!-- Trust Indicators -->
            <div class="mt-12 grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold text-white">50+</div>
                    <div class="text-blue-200">PKK Bergabung</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">1000+</div>
                    <div class="text-blue-200">Keluarga Terdaftar</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">99%</div>
                    <div class="text-blue-200">Uptime</div>
                </div>
                <div>
                    <div class="text-3xl font-bold text-white">24/7</div>
                    <div class="text-blue-200">Support</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-20 bg-gray-50">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-5xl font-bold text-gray-800 mb-6">Hubungi Kami</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Tim kami siap membantu Anda memulai perjalanan digitalisasi PKK. 
                Jangan ragu untuk menghubungi kami.
            </p>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Info -->
            <div>
                <h3 class="text-2xl font-bold text-gray-800 mb-8">Informasi Kontak</h3>
                <div class="space-y-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-1">Alamat</h4>
                            <p class="text-gray-600">Jl. PKK Raya No. 123<br>Jakarta Pusat 10110</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-1">Telepon</h4>
                            <p class="text-gray-600">+62 21 1234 5678<br>+62 812 3456 7890</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-1">Email</h4>
                            <p class="text-gray-600">info@smartpkk.id<br>support@smartpkk.id</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-clock text-primary-600"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-800 mb-1">Jam Operasional</h4>
                            <p class="text-gray-600">Senin - Jumat: 08:00 - 17:00<br>Sabtu: 08:00 - 12:00</p>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media -->
                <div class="mt-8">
                    <h4 class="font-semibold text-gray-800 mb-4">Ikuti Kami</h4>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-primary-600 text-white rounded-lg flex items-center justify-center hover:bg-primary-700 transition-colors">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-primary-600 text-white rounded-lg flex items-center justify-center hover:bg-primary-700 transition-colors">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-primary-600 text-white rounded-lg flex items-center justify-center hover:bg-primary-700 transition-colors">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-primary-600 text-white rounded-lg flex items-center justify-center hover:bg-primary-700 transition-colors">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="bg-white rounded-2xl shadow-lg p-8">
                <h3 class="text-2xl font-bold text-gray-800 mb-6">Kirim Pesan</h3>
                <form class="space-y-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Masukkan nama lengkap">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                            <input type="email" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="nama@email.com">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Subjek</label>
                        <input type="text" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Subjek pesan">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pesan</label>
                        <textarea rows="5" class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent" placeholder="Tulis pesan Anda di sini..."></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white px-8 py-4 rounded-lg font-semibold transition-all hover-lift">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-20 bg-white">
    <div class="container mx-auto px-4 lg:px-8">
        <div class="text-center mb-16">
            <h2 class="text-3xl lg:text-5xl font-bold text-gray-800 mb-6">Pertanyaan Umum</h2>
            <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                Beberapa pertanyaan yang sering diajukan tentang Smart PKK
            </p>
        </div>
        
        <div class="max-w-4xl mx-auto">
            <div class="space-y-6">
                <!-- FAQ Item 1 -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(1)">
                        <h4 class="text-lg font-semibold text-gray-800">Apakah Smart PKK gratis untuk digunakan?</h4>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform" id="faq-icon-1"></i>
                    </button>
                    <div class="mt-4 text-gray-600 hidden" id="faq-content-1">
                        Ya, Smart PKK menyediakan paket gratis dengan fitur dasar yang cukup untuk PKK kecil. 
                        Untuk fitur premium dan kapasitas yang lebih besar, tersedia paket berbayar yang terjangkau.
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(2)">
                        <h4 class="text-lg font-semibold text-gray-800">Bagaimana cara memulai menggunakan Smart PKK?</h4>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform" id="faq-icon-2"></i>
                    </button>
                    <div class="mt-4 text-gray-600 hidden" id="faq-content-2">
                        Sangat mudah! Cukup klik tombol "Daftar Sekarang", isi data PKK Anda, dan sistem akan 
                        memandu Anda untuk setup awal. Tim support kami juga siap membantu jika diperlukan.
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(3)">
                        <h4 class="text-lg font-semibold text-gray-800">Apakah data kami aman di Smart PKK?</h4>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform" id="faq-icon-3"></i>
                    </button>
                    <div class="mt-4 text-gray-600 hidden" id="faq-content-3">
                        Tentu saja! Kami menggunakan enkripsi tingkat bank dan backup otomatis. 
                        Data Anda disimpan di server yang aman dan hanya bisa diakses oleh tim PKK yang berwenang.
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(4)">
                        <h4 class="text-lg font-semibold text-gray-800">Bisakah digunakan di smartphone?</h4>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform" id="faq-icon-4"></i>
                    </button>
                    <div class="mt-4 text-gray-600 hidden" id="faq-content-4">
                        Ya! Smart PKK dirancang responsive dan bisa diakses dengan sempurna di smartphone, 
                        tablet, maupun komputer. Bahkan ada fitur offline untuk tetap bisa input data tanpa internet.
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <button class="flex items-center justify-between w-full text-left" onclick="toggleFaq(5)">
                        <h4 class="text-lg font-semibold text-gray-800">Apakah ada pelatihan untuk penggunaan sistem?</h4>
                        <i class="fas fa-chevron-down text-gray-500 transition-transform" id="faq-icon-5"></i>
                    </button>
                    <div class="mt-4 text-gray-600 hidden" id="faq-content-5">
                        Kami menyediakan pelatihan gratis untuk semua pengguna baru. Tim kami akan membantu 
                        setup awal dan memberikan tutorial lengkap hingga PKK Anda mahir menggunakan sistem.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    function toggleFaq(id) {
        const content = document.getElementById(`faq-content-${id}`);
        const icon = document.getElementById(`faq-icon-${id}`);
        
        if (content.classList.contains('hidden')) {
            content.classList.remove('hidden');
            icon.style.transform = 'rotate(180deg)';
        } else {
            content.classList.add('hidden');
            icon.style.transform = 'rotate(0deg)';
        }
    }
    
    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe all fade-in elements
    document.querySelectorAll('.fade-in').forEach(el => {
        observer.observe(el);
    });
    
    // Counter animation
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            element.textContent = Math.floor(current);
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            }
        }, 20);
    }
    
    // Initialize counters when they come into view
    const counterElements = document.querySelectorAll('[data-counter]');
    counterElements.forEach(el => {
        observer.observe(el);
    });
</script>
@endsection