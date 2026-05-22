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

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen p-4 md:p-6 font-sans overflow-x-hidden">

<!-- HEADER -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">

<div class="flex items-center gap-3">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="cpu" class="w-6 h-6 text-white"></i>
</div>
<div>
<h1 class="text-2xl md:text-4xl font-display font-bold text-gray-800">Status Device IoT</h1>
<p class="text-gray-600 mt-0.5 text-sm">Kondisi perangkat dan konektivitas sistem</p>
</div>
</div>

<div class="flex items-center gap-3 w-full sm:w-auto">
<div class="flex items-center gap-2 bg-gradient-to-r from-green-500/10 to-emerald-500/10 px-3 py-2 rounded-xl border border-green-200">
<div class="w-2 h-2 bg-green-500 rounded-full animate-pulse-slow"></div>
<span class="text-green-700 font-semibold text-xs">System Online</span>
</div>

<a href="/dashboard" class="ml-auto sm:ml-0 group bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-xl font-semibold hover:shadow-lg hover:scale-105 transition-all duration-300 flex items-center gap-2">
<i data-feather="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
<span class="text-sm md:text-base">Dashboard</span>
</a>
</div>

</div>

<!-- DEVICE GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-8">

<!-- DEVICE A -->
<div class="group bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-500 p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-green-50 to-emerald-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">

<div class="flex items-center justify-between mb-6">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="box" class="w-8 h-8 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Device Rak A</h3>
<p class="text-gray-600 text-sm">IoT Sensor Node</p>
</div>
</div>

<div class="flex flex-col items-end">
<span class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold border border-green-200">
Online
</span>
</div>
</div>

<div class="mt-6">
<p class="text-3xl font-display font-bold text-green-600 mb-2">Terhubung</p>
<p class="text-gray-600 leading-relaxed">Device aktif dan mengirim data sensor secara realtime dengan koneksi yang stabil.</p>
</div>

<div class="mt-6 pt-4 border-t border-gray-100">
<div class="flex items-center justify-between text-sm">
<span class="text-gray-500">Signal Strength:</span>
<span class="text-green-600 font-semibold">95%</span>
</div>
</div>

</div>
</div>



<!-- LORA -->
<div class="group bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-500 p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-blue-50 to-indigo-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">

<div class="flex items-center justify-between mb-6">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="radio" class="w-8 h-8 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">LoRa Gateway</h3>
<p class="text-gray-600 text-sm">Communication Hub</p>
</div>
</div>

<div class="flex flex-col items-end">
<span class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold border border-green-200">
Aktif
</span>
</div>
</div>

<div class="mt-6">
<p class="text-3xl font-display font-bold text-green-600 mb-2">Terhubung</p>
<p class="text-gray-600 leading-relaxed">Gateway menerima data dari device sensor dengan jangkauan komunikasi optimal.</p>
</div>

<div class="mt-6 pt-4 border-t border-gray-100">
<div class="flex items-center justify-between text-sm">
<span class="text-gray-500">Range:</span>
<span class="text-blue-600 font-semibold">5 km</span>
</div>
</div>

</div>
</div>

<!-- SERVER -->
<div class="group bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-500 p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">

<div class="flex items-center justify-between mb-6">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 shadow-lg">
<i data-feather="server" class="w-8 h-8 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Koneksi Server</h3>
<p class="text-gray-600 text-sm">Data Processing</p>
</div>
</div>

<div class="flex flex-col items-end">
<span class="bg-gradient-to-r from-green-500/10 to-emerald-500/10 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold border border-green-200">
Stabil
</span>
</div>
</div>

<div class="mt-6">
<p class="text-3xl font-display font-bold text-green-600 mb-2">Terhubung</p>
<p class="text-gray-600 leading-relaxed">Server menerima dan memproses data monitoring secara realtime dengan performa optimal.</p>
</div>

<div class="mt-6 pt-4 border-t border-gray-100">
<div class="flex items-center justify-between text-sm">
<span class="text-gray-500">Uptime:</span>
<span class="text-purple-600 font-semibold">99.9%</span>
</div>
</div>

</div>
</div>

</div>

<!-- SYSTEM INFO -->
<div class="mt-12 bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-100">

<div class="flex items-center gap-3 mb-8">
<div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
<i data-feather="settings" class="w-6 h-6 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Status Sistem</h3>
<p class="text-gray-600">Kondisi operasional perangkat IoT</p>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<div class="group bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="check-circle" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800">Semua device aktif</p>
<p class="text-green-600 text-sm">3/3 perangkat online</p>
</div>
</div>
</div>

<div class="group bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="radio" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-blue-800">Komunikasi LoRa berjalan baik</p>
<p class="text-blue-600 text-sm">Koneksi stabil</p>
</div>
</div>
</div>

<div class="group bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-2xl border border-purple-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="clock" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800">Update status</p>
<p class="text-purple-600 text-sm">5 detik lalu</p>
</div>
</div>
</div>

</div>

</div>

<script>
feather.replace()
</script>

</body>
</html>