<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Status Device - Smart Rack Security</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .glass { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(255, 255, 255, 0.2); }
  .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
</style>
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 font-sans overflow-x-hidden">

<div class="flex h-screen overflow-hidden">

<!-- OVERLAY untuk mobile -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<aside id="sidebar" class="fixed md:static z-40 top-0 left-0 h-full w-72 bg-gradient-to-b from-slate-900 via-gray-900 to-slate-800 text-gray-200 shadow-2xl p-6 transform -translate-x-full md:translate-x-0 transition-all duration-300 border-r border-gray-700/50 overflow-y-auto">

<!-- LOGO -->
<div class="flex items-center gap-4 mb-12 p-3 bg-gradient-to-r from-indigo-600/20 to-purple-600/20 rounded-2xl border border-indigo-500/20">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
<i data-feather="shield" class="w-6 h-6 text-white"></i>
</div>
<div>
<h2 class="text-xl font-display font-bold text-white leading-none">Smart Rack</h2>
<p class="text-sm text-indigo-300">Security System</p>
</div>
</div>

<!-- MENU -->
<nav class="space-y-3">
<a href="/" class="group flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-slate-700/50 hover:to-slate-600/50 hover:text-white transition-all duration-300 text-gray-300">
<div class="w-10 h-10 bg-gray-700/50 group-hover:bg-indigo-500/20 rounded-lg flex items-center justify-center transition-all duration-300">
<i data-feather="arrow-left" class="w-5 h-5"></i>
</div>
<span class="font-medium">Home</span>
</a>

<a href="/dashboard" class="group flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-slate-700/50 hover:to-slate-600/50 hover:text-white transition-all duration-300 text-gray-300">
<div class="w-10 h-10 bg-gray-700/50 group-hover:bg-indigo-500/20 rounded-lg flex items-center justify-center transition-all duration-300">
<i data-feather="home" class="w-5 h-5"></i>
</div>
<span class="font-medium">Dashboard</span>
</a>

<a href="/monitoring" class="group flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-slate-700/50 hover:to-slate-600/50 hover:text-white transition-all duration-300 text-gray-300">
<div class="w-10 h-10 bg-gray-700/50 group-hover:bg-blue-500/20 rounded-lg flex items-center justify-center transition-all duration-300">
<i data-feather="activity" class="w-5 h-5"></i>
</div>
<span class="font-medium">Monitoring Sensor</span>
</a>

<a href="/log" class="group flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-slate-700/50 hover:to-slate-600/50 hover:text-white transition-all duration-300 text-gray-300">
<div class="w-10 h-10 bg-gray-700/50 group-hover:bg-green-500/20 rounded-lg flex items-center justify-center transition-all duration-300">
<i data-feather="file-text" class="w-5 h-5"></i>
</div>
<span class="font-medium">Log Aktivitas</span>
</a>

<a href="/device" class="group flex items-center gap-4 px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg">
<div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
<i data-feather="cpu" class="w-5 h-5"></i>
</div>
<span class="font-medium">Status Device</span>
</a>
</nav>

<!-- DIVIDER -->
<div class="border-t border-gray-700/50 my-8"></div>

<!-- USER -->
<div class="flex items-center gap-4 p-4 bg-gradient-to-r from-gray-800/50 to-slate-700/50 rounded-2xl border border-gray-700/30">
<div class="relative">
@if(auth()->user()->profile_photo)
<img src="{{ auth()->user()->profile_photo_url }}" class="w-12 h-12 rounded-xl shadow-lg object-cover">
@else
<div class="w-12 h-12 rounded-xl shadow-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
<span class="text-white font-bold text-sm">{{ auth()->user()->initials }}</span>
</div>
@endif
<div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-slate-800"></div>
</div>
<div class="flex-1">
<p class="font-semibold text-white">{{ auth()->user()->name }}</p>
<p class="text-sm text-gray-400">System Operator</p>
</div>
<a href="{{ route('logout') }}" class="w-10 h-10 bg-red-500/20 hover:bg-red-500/30 rounded-lg flex items-center justify-center text-red-400 hover:text-red-300 transition-all duration-300" title="Logout">
<i data-feather="log-out" class="w-5 h-5"></i>
</a>
</div>

</aside>

<!-- MAIN -->
<div class="flex-1 flex flex-col">

<!-- TOPBAR -->
<header class="glass border-b border-white/20 flex justify-between items-center px-4 md:px-8 py-4 md:py-6">
<div class="flex items-center gap-3">
<button onclick="toggleSidebar()" class="md:hidden w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-all">
<i data-feather="menu" class="w-5 h-5"></i>
</button>
<div>
<h1 class="text-lg md:text-2xl font-display font-bold text-gray-800">Status Device IoT</h1>
<p class="text-gray-600 mt-0.5 text-xs md:text-sm hidden sm:block">Kondisi perangkat dan konektivitas sistem</p>
</div>
</div>
<div class="flex items-center gap-2 md:gap-4">
<div class="flex items-center gap-2 bg-gradient-to-r from-green-500/10 to-emerald-500/10 px-3 py-2 rounded-xl border border-green-200">
<div class="w-2 h-2 md:w-3 md:h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
<span class="text-green-700 font-semibold text-xs md:text-sm hidden sm:inline">System Online</span>
</div>
<div class="flex items-center gap-2">
<div class="relative">
@if(auth()->user()->profile_photo)
<img src="{{ auth()->user()->profile_photo_url }}" class="w-9 h-9 md:w-11 md:h-11 rounded-xl shadow-lg object-cover">
@else
<div class="w-9 h-9 md:w-11 md:h-11 rounded-xl shadow-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
<span class="text-white font-bold text-xs md:text-sm">{{ auth()->user()->initials }}</span>
</div>
@endif
<div class="absolute -bottom-1 -right-1 w-3 h-3 md:w-4 md:h-4 bg-green-500 rounded-full border-2 border-white"></div>
</div>
<div class="hidden sm:block">
<span class="text-gray-800 font-semibold text-sm">{{ auth()->user()->name }}</span>
<p class="text-gray-500 text-xs">Online</p>
</div>
</div>
</div>
</header>

<!-- CONTENT -->
<main class="p-4 md:p-8 space-y-6 overflow-y-auto">

<!-- DEVICE GRID -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 md:gap-6 lg:gap-8">

<!-- DEVICE A -->
<div class="group bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-500 p-5 sm:p-6 md:p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-4 sm:mb-6">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl md:rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg flex-shrink-0">
<i data-feather="box" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">Device Rak A</h3>
<p class="text-gray-600 text-xs sm:text-sm">IoT Sensor Node</p>
</div>
</div>
<span class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 text-green-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold border border-green-200 flex-shrink-0">Online</span>
</div>
<p class="text-xl sm:text-2xl md:text-3xl font-display font-bold text-green-600 mb-1 sm:mb-2">Terhubung</p>
<p class="text-gray-600 text-sm leading-relaxed">Device aktif dan mengirim data sensor secara realtime dengan koneksi yang stabil.</p>
<div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-100 flex items-center justify-between text-sm">
<span class="text-gray-500 text-xs sm:text-sm">Signal Strength:</span>
<span class="text-green-600 font-semibold text-xs sm:text-sm">95%</span>
</div>
</div>
</div>

<!-- LORA -->
<div class="group bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-500 p-5 sm:p-6 md:p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-4 sm:mb-6">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl md:rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg flex-shrink-0">
<i data-feather="radio" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">LoRa Gateway</h3>
<p class="text-gray-600 text-xs sm:text-sm">Communication Hub</p>
</div>
</div>
<span class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 text-green-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold border border-green-200 flex-shrink-0">Aktif</span>
</div>
<p class="text-xl sm:text-2xl md:text-3xl font-display font-bold text-green-600 mb-1 sm:mb-2">Terhubung</p>
<p class="text-gray-600 text-sm leading-relaxed">Gateway menerima data dari device sensor dengan jangkauan komunikasi optimal.</p>
<div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-100 flex items-center justify-between">
<span class="text-gray-500 text-xs sm:text-sm">Range:</span>
<span class="text-blue-600 font-semibold text-xs sm:text-sm">5 km</span>
</div>
</div>
</div>

<!-- SERVER -->
<div class="group bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-[1.02] transition-all duration-500 p-5 sm:p-6 md:p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-4 sm:mb-6">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-12 h-12 sm:w-14 sm:h-14 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl md:rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg flex-shrink-0">
<i data-feather="server" class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">Koneksi Server</h3>
<p class="text-gray-600 text-xs sm:text-sm">Data Processing</p>
</div>
</div>
<span class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 text-green-700 px-3 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold border border-green-200 flex-shrink-0">Stabil</span>
</div>
<p class="text-xl sm:text-2xl md:text-3xl font-display font-bold text-green-600 mb-1 sm:mb-2">Terhubung</p>
<p class="text-gray-600 text-sm leading-relaxed">Server menerima dan memproses data monitoring secara realtime dengan performa optimal.</p>
<div class="mt-4 sm:mt-6 pt-3 sm:pt-4 border-t border-gray-100 flex items-center justify-between">
<span class="text-gray-500 text-xs sm:text-sm">Uptime:</span>
<span class="text-purple-600 font-semibold text-xs sm:text-sm">99.9%</span>
</div>
</div>
</div>

</div>

<!-- SYSTEM INFO -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100">
<div class="flex items-center gap-3 mb-5 sm:mb-8">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="settings" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">Status Sistem</h3>
<p class="text-gray-600 text-xs sm:text-sm">Kondisi operasional perangkat IoT</p>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
<div class="group bg-gradient-to-br from-green-50 to-emerald-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-green-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
<i data-feather="check-circle" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800 text-sm sm:text-base">Semua device aktif</p>
<p class="text-green-600 text-xs sm:text-sm">3/3 perangkat online</p>
</div>
</div>
</div>
<div class="group bg-gradient-to-br from-blue-50 to-indigo-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-blue-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
<i data-feather="radio" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-blue-800 text-sm sm:text-base">Komunikasi LoRa berjalan baik</p>
<p class="text-blue-600 text-xs sm:text-sm">Koneksi stabil</p>
</div>
</div>
</div>
<div class="group bg-gradient-to-br from-purple-50 to-pink-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-purple-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
<i data-feather="clock" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800 text-sm sm:text-base">Update status</p>
<p class="text-purple-600 text-xs sm:text-sm">5 detik lalu</p>
</div>
</div>
</div>
</div>
</div>

</main>
</div>
</div>

<script>
feather.replace()

function toggleSidebar() {
  const sidebar = document.getElementById('sidebar');
  const overlay = document.getElementById('sidebar-overlay');
  const isOpen = !sidebar.classList.contains('-translate-x-full');
  if (isOpen) {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
  } else {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
  }
}
</script>

</body>
</html>