<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Smart Rack Security - Sistem Monitoring IoT</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .gradient-text { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  .glass { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); }
  .animate-float { animation: float 6s ease-in-out infinite; }
  @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
</style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 font-sans text-gray-800 overflow-x-hidden">

<!-- NAVBAR -->
<nav class="glass fixed top-0 w-full z-50 border-b border-white/10">
<div class="max-w-7xl mx-auto flex justify-between items-center px-6 py-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
<i data-feather="shield" class="w-5 h-5 text-white"></i>
</div>
<h1 class="text-xl font-display font-bold text-gray-800">Smart Rack Security</h1>
</div>

<div class="hidden md:flex items-center gap-8">
<a href="/home" class="text-gray-700 hover:text-indigo-600 transition-colors font-medium">Home</a>
<a href="#fitur" class="text-gray-700 hover:text-indigo-600 transition-colors font-medium">Sensor</a>
<a href="#tentang" class="text-gray-700 hover:text-indigo-600 transition-colors font-medium">Tentang</a>
<a href="/login" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2.5 rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-300 font-medium">
Login
</a>
</div>

<button class="md:hidden p-2 rounded-lg hover:bg-white/20 transition-colors">
<i data-feather="menu" class="w-6 h-6 text-gray-800"></i>
</button>
</div>
</nav>

<!-- HERO -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden pt-20">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-600 via-purple-700 to-blue-800"></div>
<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>

<div class="relative max-w-7xl mx-auto text-center px-6 z-10">
<div class="animate-float mb-8">
<div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/20">
<i data-feather="shield" class="w-10 h-10 text-white"></i>
</div>
</div>

<h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-display font-bold text-white leading-tight mb-6">
Sistem Monitoring
<span class="block gradient-text bg-gradient-to-r from-blue-200 to-purple-200 bg-clip-text text-transparent">
Keamanan IoT
</span>
</h1>

<p class="text-base sm:text-lg md:text-xl lg:text-2xl text-blue-100 max-w-3xl mx-auto mb-8 md:mb-12 leading-relaxed px-4">
Platform monitoring keamanan rak berbasis Internet of Things dengan sensor PIR, getaran SW-420, reed switch, dan komunikasi LoRa untuk pengawasan realtime.
</p>

<div class="flex flex-col sm:flex-row justify-center gap-6 mb-16">
<a href="/dashboard" class="group bg-white text-indigo-600 px-8 py-4 rounded-2xl font-semibold hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center justify-center gap-3">
<span>Masuk Dashboard</span>
<i data-feather="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
</a>

<a href="#fitur" class="glass text-white px-8 py-4 rounded-2xl font-semibold hover:bg-white/20 transition-all duration-300 flex items-center justify-center gap-3">
<span>Pelajari Sistem</span>
<i data-feather="chevron-down" class="w-5 h-5"></i>
</a>
</div>

<!-- Floating Elements -->
<div class="absolute top-20 left-10 w-4 h-4 bg-blue-400 rounded-full animate-pulse opacity-60"></div>
<div class="absolute top-40 right-20 w-6 h-6 bg-purple-400 rounded-full animate-bounce opacity-40"></div>
<div class="absolute bottom-20 left-20 w-3 h-3 bg-indigo-300 rounded-full animate-ping opacity-50"></div>
</div>
</section>

<!-- STATISTIK -->
<section class="py-20 bg-white relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-r from-blue-50 to-indigo-50 opacity-50"></div>

<div class="relative max-w-7xl mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl font-display font-bold text-gray-800 mb-4">Sistem Terdepan</h2>
<p class="text-xl text-gray-600">Teknologi monitoring keamanan yang dapat diandalkan</p>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-8">
<div class="group text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100">
<div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="layers" class="w-8 h-8 text-white"></i>
</div>
<h3 class="text-3xl font-display font-bold text-indigo-600 mb-2">4</h3>
<p class="text-gray-600 font-medium">Sensor Sistem</p>
</div>

<div class="group text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100">
<div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="zap" class="w-8 h-8 text-white"></i>
</div>
<h3 class="text-3xl font-display font-bold text-green-600 mb-2">Realtime</h3>
<p class="text-gray-600 font-medium">Monitoring</p>
</div>

<div class="group text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100">
<div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="radio" class="w-8 h-8 text-white"></i>
</div>
<h3 class="text-3xl font-display font-bold text-blue-600 mb-2">LoRa</h3>
<p class="text-gray-600 font-medium">Komunikasi</p>
</div>

<div class="group text-center p-8 bg-white rounded-2xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100">
<div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="clock" class="w-8 h-8 text-white"></i>
</div>
<h3 class="text-3xl font-display font-bold text-purple-600 mb-2">6 Jam</h3>
<p class="text-gray-600 font-medium">Keamanan</p>
</div>
</div>
</div>
</section>

<!-- SENSOR -->
<section id="fitur" class="py-24 bg-gradient-to-br from-gray-50 to-blue-50 relative overflow-hidden">
<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23f1f5f9" fill-opacity="0.4"%3E%3Cpath d="M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z"/%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>

<div class="relative max-w-7xl mx-auto px-6">
<div class="text-center mb-16">
<h2 class="text-4xl md:text-5xl font-display font-bold text-gray-800 mb-4">Sensor Monitoring Sistem</h2>
<p class="text-xl text-gray-600 max-w-2xl mx-auto">Teknologi sensor terdepan untuk monitoring keamanan yang komprehensif</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 md:gap-8">
<!-- PIR -->
<div class="group bg-white p-8 rounded-3xl shadow-lg hover:shadow-2xl hover:-translate-y-4 transition-all duration-500 text-center border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-purple-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="w-20 h-20 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="activity" class="w-10 h-10 text-white"></i>
</div>
<h4 class="font-display font-bold text-xl mb-4 text-gray-800">Sensor PIR</h4>
<p class="text-gray-600 leading-relaxed">
Mendeteksi pergerakan manusia di sekitar rak untuk mengidentifikasi aktivitas mencurigakan dengan akurasi tinggi.
</p>
</div>
</div>

<!-- GETAR -->
<div class="group bg-white p-8 rounded-3xl shadow-lg hover:shadow-2xl hover:-translate-y-4 transition-all duration-500 text-center border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-cyan-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="zap" class="w-10 h-10 text-white"></i>
</div>
<h4 class="font-display font-bold text-xl mb-4 text-gray-800">Sensor Getar</h4>
<p class="text-gray-600 leading-relaxed">
Sensor SW-420 mendeteksi getaran ketika rak digeser, didorong, atau disentuh dengan sensitivitas optimal.
</p>
</div>
</div>

<!-- REED -->
<div class="group bg-white p-8 rounded-3xl shadow-lg hover:shadow-2xl hover:-translate-y-4 transition-all duration-500 text-center border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="unlock" class="w-10 h-10 text-white"></i>
</div>
<h4 class="font-display font-bold text-xl mb-4 text-gray-800">Reed Switch</h4>
<p class="text-gray-600 leading-relaxed">
Mengetahui kondisi rak terbuka atau tertutup secara otomatis dengan respons yang cepat dan akurat.
</p>
</div>
</div>

<!-- LORA -->
<div class="group bg-white p-8 rounded-3xl shadow-lg hover:shadow-2xl hover:-translate-y-4 transition-all duration-500 text-center border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-6 group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="radio" class="w-10 h-10 text-white"></i>
</div>
<h4 class="font-display font-bold text-xl mb-4 text-gray-800">Komunikasi LoRa</h4>
<p class="text-gray-600 leading-relaxed">
Mengirim data sensor ke server monitoring dengan jangkauan komunikasi jarak jauh dan konsumsi daya rendah.
</p>
</div>
</div>
</div>
</div>
</section>

<!-- TENTANG -->
<section id="tentang" class="py-24 bg-white relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-purple-50 opacity-30"></div>

<div class="relative max-w-5xl mx-auto text-center px-6">
<div class="mb-16">
<h2 class="text-4xl md:text-5xl font-display font-bold text-gray-800 mb-6">Tentang Sistem</h2>
<div class="w-24 h-1 bg-gradient-to-r from-indigo-500 to-purple-600 mx-auto rounded-full mb-8"></div>
</div>

<div class="bg-white/80 backdrop-blur-sm rounded-3xl p-12 shadow-2xl border border-gray-100">
<p class="text-xl text-gray-700 leading-relaxed mb-8">
Smart Rack Security merupakan sistem keamanan berbasis <span class="font-semibold text-indigo-600">Internet of Things</span> yang
dirancang untuk meningkatkan pengawasan barang pada rak penyimpanan dengan teknologi terdepan.
</p>

<div class="grid md:grid-cols-3 gap-8 mt-12">
<div class="text-center">
<div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
<i data-feather="cpu" class="w-8 h-8 text-white"></i>
</div>
<h4 class="font-display font-semibold text-lg text-gray-800 mb-2">Sensor Terintegrasi</h4>
<p class="text-gray-600 text-sm">PIR, SW-420, Reed Switch</p>
</div>

<div class="text-center">
<div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
<i data-feather="zap" class="w-8 h-8 text-white"></i>
</div>
<h4 class="font-display font-semibold text-lg text-gray-800 mb-2">Monitoring Realtime</h4>
<p class="text-gray-600 text-sm">Pengawasan otomatis saat toko buka</p>
</div>

<div class="text-center">
<div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
<i data-feather="radio" class="w-8 h-8 text-white"></i>
</div>
<h4 class="font-display font-semibold text-lg text-gray-800 mb-2">Komunikasi LoRa</h4>
<p class="text-gray-600 text-sm">Jangkauan jauh, daya rendah</p>
</div>
</div>
</div>
</div>
</section>

<!-- CTA -->
<section class="py-24 bg-gradient-to-br from-indigo-600 via-purple-700 to-blue-800 text-white text-center relative overflow-hidden">
<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>

<div class="relative max-w-4xl mx-auto px-6">
<div class="mb-8">
<div class="w-16 h-16 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto mb-6 border border-white/20">
<i data-feather="monitor" class="w-8 h-8 text-white"></i>
</div>
<h2 class="text-4xl md:text-5xl font-display font-bold mb-6">Mulai Monitoring Sekarang</h2>
<p class="text-xl text-blue-100 max-w-2xl mx-auto mb-10">
Akses dashboard monitoring untuk mengawasi keamanan rak Anda secara realtime dengan teknologi IoT terdepan.
</p>
</div>

<div class="flex flex-col sm:flex-row justify-center gap-6">
<a href="/dashboard" class="group bg-white text-indigo-600 px-10 py-4 rounded-2xl font-semibold hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center justify-center gap-3">
<span>Buka Dashboard</span>
<i data-feather="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
</a>

<a href="/login" class="glass text-white px-10 py-4 rounded-2xl font-semibold hover:bg-white/20 transition-all duration-300 flex items-center justify-center gap-3">
<span>Login Admin</span>
<i data-feather="user" class="w-5 h-5"></i>
</a>
</div>
</div>
</section>

<!-- FOOTER -->
<footer class="bg-gradient-to-br from-slate-900 via-gray-900 to-slate-800 text-gray-300 py-16 relative overflow-hidden">
<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="40" height="40" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="%23ffffff" fill-opacity="0.02"%3E%3Cpath d="M20 20c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10zm10 0c0-5.5-4.5-10-10-10s-10 4.5-10 10 4.5 10 10 10 10-4.5 10-10z"/%3E%3C/g%3E%3C/svg%3E')] opacity-30"></div>

<div class="relative max-w-7xl mx-auto px-6">
<div class="text-center mb-12">
<div class="flex items-center justify-center gap-3 mb-6">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
<i data-feather="shield" class="w-6 h-6 text-white"></i>
</div>
<h3 class="text-2xl font-display font-bold text-white">Smart Rack Security</h3>
</div>
<p class="text-gray-400 max-w-2xl mx-auto leading-relaxed">
Sistem monitoring keamanan rak berbasis IoT dengan teknologi sensor terdepan untuk pengawasan realtime yang dapat diandalkan.
</p>
</div>

<div class="grid md:grid-cols-3 gap-8 mb-12">
<div class="text-center">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-4">
<i data-feather="cpu" class="w-6 h-6 text-white"></i>
</div>
<h4 class="font-semibold text-white mb-2">Teknologi IoT</h4>
<p class="text-gray-400 text-sm">Sensor terintegrasi dengan komunikasi LoRa</p>
</div>

<div class="text-center">
<div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center mx-auto mb-4">
<i data-feather="monitor" class="w-6 h-6 text-white"></i>
</div>
<h4 class="font-semibold text-white mb-2">Dashboard Modern</h4>
<p class="text-gray-400 text-sm">Interface monitoring yang intuitif</p>
</div>

<div class="text-center">
<div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mx-auto mb-4">
<i data-feather="shield-check" class="w-6 h-6 text-white"></i>
</div>
<h4 class="font-semibold text-white mb-2">Keamanan Saat Toko Buka</h4>
<p class="text-gray-400 text-sm">Pengawasan otomatis selama 6 jam operasional</p>
</div>
</div>

<div class="border-t border-gray-700 pt-8 text-center">
<p class="text-gray-400 text-sm">
© 2026 Smart Rack Security System. Monitoring Keamanan Rak Barang dengan Teknologi IoT.
</p>
</div>
</div>
</footer>

<script>
feather.replace()
</script>

</body>
</html>