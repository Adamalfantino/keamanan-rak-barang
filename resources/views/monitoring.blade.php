<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Monitoring Sensor - Smart Rack Security</title>
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

<a href="/monitoring" class="group flex items-center gap-4 px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg">
<div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
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

<a href="/device" class="group flex items-center gap-4 px-4 py-3 rounded-xl hover:bg-gradient-to-r hover:from-slate-700/50 hover:to-slate-600/50 hover:text-white transition-all duration-300 text-gray-300">
<div class="w-10 h-10 bg-gray-700/50 group-hover:bg-purple-500/20 rounded-lg flex items-center justify-center transition-all duration-300">
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
<h1 class="text-lg md:text-2xl font-display font-bold text-gray-800">Monitoring Sensor</h1>
<p class="text-gray-600 mt-0.5 text-xs md:text-sm hidden sm:block">Data realtime dari sensor keamanan rak</p>
</div>
</div>
<div class="flex items-center gap-2 md:gap-4">
<div class="flex items-center gap-2 bg-gradient-to-r from-green-500/10 to-emerald-500/10 px-3 py-2 rounded-xl border border-green-200">
<div id="polling-dot" class="w-2 h-2 md:w-3 md:h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
<span class="text-green-700 font-semibold text-xs md:text-sm hidden sm:inline">Live Monitoring</span>
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
<main class="p-4 md:p-8 space-y-6 md:space-y-8 overflow-y-auto">

<!-- STATISTIK 24 JAM -->
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6">
<div class="bg-white rounded-2xl shadow p-4 md:p-6 border border-gray-100 flex items-center gap-3 md:gap-4">
<div class="w-12 h-12 md:w-14 md:h-14 bg-indigo-100 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="eye" class="w-6 h-6 md:w-7 md:h-7 text-indigo-600"></i>
</div>
<div>
<p class="text-gray-500 text-xs md:text-sm">Gerakan (24 jam)</p>
<p class="text-2xl md:text-3xl font-display font-bold text-indigo-600">{{ $pirCount24h }}</p>
</div>
</div>
<div class="bg-white rounded-2xl shadow p-4 md:p-6 border border-gray-100 flex items-center gap-3 md:gap-4">
<div class="w-12 h-12 md:w-14 md:h-14 bg-yellow-100 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="zap" class="w-6 h-6 md:w-7 md:h-7 text-yellow-600"></i>
</div>
<div>
<p class="text-gray-500 text-xs md:text-sm">Getaran (24 jam)</p>
<p class="text-2xl md:text-3xl font-display font-bold text-yellow-600">{{ $vibrationCount24h }}</p>
</div>
</div>
<div class="bg-white rounded-2xl shadow p-4 md:p-6 border border-gray-100 flex items-center gap-3 md:gap-4">
<div class="w-12 h-12 md:w-14 md:h-14 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="unlock" class="w-6 h-6 md:w-7 md:h-7 text-purple-600"></i>
</div>
<div>
<p class="text-gray-500 text-xs md:text-sm">Rak Dibuka (24 jam)</p>
<p class="text-2xl md:text-3xl font-display font-bold text-purple-600">{{ $reedCount24h }}</p>
</div>
</div>
</div>

<!-- SENSOR GRID -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 lg:gap-8">

<!-- PIR -->
<div class="group bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 p-5 md:p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-blue-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-4 md:mb-6">
<div class="flex items-center gap-3 md:gap-4">
<div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="eye" class="w-6 h-6 md:w-8 md:h-8 text-white"></i>
</div>
<div>
<h3 class="text-lg md:text-xl font-display font-bold text-gray-800">Sensor PIR</h3>
<p class="text-gray-500 text-xs md:text-sm">Motion Detection</p>
</div>
</div>
@if($pirSensor && $pirSensor->status === 'active')
<span class="bg-green-100 text-green-700 px-2 py-1 md:px-3 rounded-xl text-xs font-semibold border border-green-200">Aktif</span>
@else
<span class="bg-red-100 text-red-700 px-2 py-1 md:px-3 rounded-xl text-xs font-semibold border border-red-200">Tidak Aktif</span>
@endif
</div>
@if($pirLatest)
<div class="mb-3 md:mb-4">
<p id="pir-status-text" class="text-xl md:text-3xl font-display font-bold {{ $pirLatest->motion_detected ? 'text-red-600' : 'text-green-600' }} mb-1">
{{ $pirLatest->motion_detected ? '⚠ Gerakan' : '✓ Aman' }}
</p>
<p id="pir-detail" class="text-gray-500 text-xs md:text-sm">
Intensitas: <span class="font-semibold text-gray-700">{{ $pirLatest->motion_intensity ?? 0 }}%</span>
· Durasi: <span class="font-semibold text-gray-700">{{ $pirLatest->duration_seconds ?? 0 }}s</span>
</p>
</div>
<div class="pt-3 border-t border-gray-100 flex justify-between text-xs md:text-sm">
<span class="text-gray-500">Update:</span>
<span id="pir-update" class="{{ $pirLatest->motion_detected ? 'text-red-600' : 'text-green-600' }} font-semibold">{{ $pirLatest->recorded_at->diffForHumans() }}</span>
</div>
@else
<p class="text-lg md:text-2xl font-display font-bold text-gray-400 mb-2">Belum Ada Data</p>
<p class="text-gray-500 text-xs md:text-sm">Menunggu data dari sensor...</p>
@endif
</div>
</div>

<!-- VIBRATION -->
<div class="group bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 p-5 md:p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-yellow-50 to-orange-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-4 md:mb-6">
<div class="flex items-center gap-3 md:gap-4">
<div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="zap" class="w-6 h-6 md:w-8 md:h-8 text-white"></i>
</div>
<div>
<h3 class="text-lg md:text-xl font-display font-bold text-gray-800">Sensor Getar</h3>
<p class="text-gray-500 text-xs md:text-sm">SW-420 Vibration</p>
</div>
</div>
@if($vibrationSensor && $vibrationSensor->status === 'active')
<span class="bg-green-100 text-green-700 px-2 py-1 md:px-3 rounded-xl text-xs font-semibold border border-green-200">Aktif</span>
@else
<span class="bg-red-100 text-red-700 px-2 py-1 md:px-3 rounded-xl text-xs font-semibold border border-red-200">Tidak Aktif</span>
@endif
</div>
@if($vibrationLatest)
<div class="mb-3 md:mb-4">
<p id="vib-status-text" class="text-xl md:text-3xl font-display font-bold {{ $vibrationLatest->is_abnormal ? 'text-red-600' : 'text-green-600' }} mb-1">
{{ $vibrationLatest->is_abnormal ? '⚠ Abnormal' : '✓ Stabil' }}
</p>
<p class="text-gray-500 text-xs md:text-sm">
Magnitude: <span class="font-semibold text-gray-700">{{ number_format($vibrationLatest->magnitude ?? 0, 2) }}</span>
· Status: <span class="font-semibold text-gray-700">{{ ucfirst($vibrationLatest->status ?? '-') }}</span>
</p>
</div>
<div class="pt-3 border-t border-gray-100 flex justify-between text-xs md:text-sm">
<span class="text-gray-500">Update:</span>
<span id="vib-update" class="{{ $vibrationLatest->is_abnormal ? 'text-red-600' : 'text-green-600' }} font-semibold">{{ $vibrationLatest->recorded_at->diffForHumans() }}</span>
</div>
@else
<p class="text-lg md:text-2xl font-display font-bold text-gray-400 mb-2">Belum Ada Data</p>
<p class="text-gray-500 text-xs md:text-sm">Menunggu data dari sensor...</p>
@endif
</div>
</div>

<!-- REED SWITCH -->
<div class="group bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 p-5 md:p-8 border border-gray-100 relative overflow-hidden sm:col-span-2 lg:col-span-1">
<div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-4 md:mb-6">
<div class="flex items-center gap-3 md:gap-4">
<div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl md:rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="unlock" class="w-6 h-6 md:w-8 md:h-8 text-white"></i>
</div>
<div>
<h3 class="text-lg md:text-xl font-display font-bold text-gray-800">Reed Switch</h3>
<p class="text-gray-500 text-xs md:text-sm">Door/Lock Status</p>
</div>
</div>
@if($reedSensor && $reedSensor->status === 'active')
<span class="bg-green-100 text-green-700 px-2 py-1 md:px-3 rounded-xl text-xs font-semibold border border-green-200">Aktif</span>
@else
<span class="bg-red-100 text-red-700 px-2 py-1 md:px-3 rounded-xl text-xs font-semibold border border-red-200">Tidak Aktif</span>
@endif
</div>
@if($reedLatest)
<div class="mb-3 md:mb-4">
<p id="reed-status-text" class="text-xl md:text-3xl font-display font-bold {{ $reedLatest->door_open ? 'text-red-600' : 'text-green-600' }} mb-1">
{{ $reedLatest->door_open ? '⚠ Terbuka' : '✓ Tertutup' }}
</p>
<p class="text-gray-500 text-xs md:text-sm">
Level: <span class="font-semibold text-gray-700">{{ ucfirst($reedLatest->access_level ?? '-') }}</span>
· Durasi: <span class="font-semibold text-gray-700">{{ $reedLatest->open_duration_seconds ?? 0 }}s</span>
</p>
</div>
<div class="pt-3 border-t border-gray-100 flex justify-between text-xs md:text-sm">
<span class="text-gray-500">Update:</span>
<span id="reed-update" class="{{ $reedLatest->door_open ? 'text-red-600' : 'text-green-600' }} font-semibold">{{ $reedLatest->recorded_at->diffForHumans() }}</span>
</div>
@else
<p class="text-lg md:text-2xl font-display font-bold text-gray-400 mb-2">Belum Ada Data</p>
<p class="text-gray-500 text-xs md:text-sm">Menunggu data dari sensor...</p>
@endif
</div>
</div>

</div>

<!-- RIWAYAT DATA TERBARU -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">

<!-- Riwayat PIR -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100">
<div class="flex items-center gap-3 mb-4 sm:mb-5">
<div class="w-9 h-9 sm:w-10 sm:h-10 bg-indigo-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="eye" class="w-4 h-4 sm:w-5 sm:h-5 text-indigo-600"></i>
</div>
<h4 class="font-display font-bold text-gray-800 text-sm sm:text-base">Riwayat PIR</h4>
</div>
<div class="space-y-3">
@forelse($pirHistory as $pir)
<div class="flex items-center justify-between p-3 rounded-xl {{ $pir->motion_detected ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100' }}">
<div>
<p class="text-sm font-semibold {{ $pir->motion_detected ? 'text-red-700' : 'text-gray-700' }}">
{{ $pir->motion_detected ? 'Gerakan' : 'Aman' }}
@if($pir->motion_detected) — {{ $pir->motion_intensity }}% @endif
</p>
<p class="text-xs text-gray-400">{{ $pir->recorded_at->format('d M H:i:s') }}</p>
</div>
<div class="w-2 h-2 rounded-full {{ $pir->motion_detected ? 'bg-red-500' : 'bg-green-500' }}"></div>
</div>
@empty
<p class="text-gray-400 text-sm text-center py-4">Belum ada data</p>
@endforelse
</div>
</div>

<!-- Riwayat Vibration -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100">
<div class="flex items-center gap-3 mb-4 sm:mb-5">
<div class="w-9 h-9 sm:w-10 sm:h-10 bg-yellow-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="zap" class="w-4 h-4 sm:w-5 sm:h-5 text-yellow-600"></i>
</div>
<h4 class="font-display font-bold text-gray-800 text-sm sm:text-base">Riwayat Getaran</h4>
</div>
<div class="space-y-3">
@forelse($vibrationHistory as $vib)
<div class="flex items-center justify-between p-3 rounded-xl {{ $vib->is_abnormal ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100' }}">
<div>
<p class="text-sm font-semibold {{ $vib->is_abnormal ? 'text-red-700' : 'text-gray-700' }}">
{{ ucfirst($vib->status) }} — {{ number_format($vib->magnitude, 2) }}
</p>
<p class="text-xs text-gray-400">{{ $vib->recorded_at->format('d M H:i:s') }}</p>
</div>
<div class="w-2 h-2 rounded-full {{ $vib->is_abnormal ? 'bg-red-500' : 'bg-green-500' }}"></div>
</div>
@empty
<p class="text-gray-400 text-sm text-center py-4">Belum ada data</p>
@endforelse
</div>
</div>

<!-- Riwayat Reed Switch -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg p-4 sm:p-5 md:p-6 border border-gray-100">
<div class="flex items-center gap-3 mb-4 sm:mb-5">
<div class="w-9 h-9 sm:w-10 sm:h-10 bg-purple-100 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="unlock" class="w-4 h-4 sm:w-5 sm:h-5 text-purple-600"></i>
</div>
<h4 class="font-display font-bold text-gray-800 text-sm sm:text-base">Riwayat Reed Switch</h4>
</div>
<div class="space-y-3">
@forelse($reedHistory as $reed)
<div class="flex items-center justify-between p-3 rounded-xl {{ $reed->door_open ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100' }}">
<div>
<p class="text-sm font-semibold {{ $reed->door_open ? 'text-red-700' : 'text-gray-700' }}">
{{ $reed->door_open ? 'Terbuka' : 'Tertutup' }} — {{ ucfirst($reed->access_level ?? 'normal') }}
</p>
<p class="text-xs text-gray-400">{{ $reed->recorded_at->format('d M H:i:s') }}</p>
</div>
<div class="w-2 h-2 rounded-full {{ $reed->door_open ? 'bg-red-500' : 'bg-green-500' }}"></div>
</div>
@empty
<p class="text-gray-400 text-sm text-center py-4">Belum ada data</p>
@endforelse
</div>
</div>

</div>

<!-- STATUS BAR -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100">
<div class="flex items-center gap-3 mb-4 sm:mb-6">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="monitor" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">Status Sistem</h3>
<p class="text-gray-600 text-xs sm:text-sm">Kondisi operasional monitoring</p>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
<div class="bg-gradient-to-br from-green-50 to-emerald-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-green-200">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="check-circle" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800 text-sm sm:text-base">Sensor aktif</p>
<p class="text-green-600 text-xs sm:text-sm">{{ \App\Models\Sensor::where('status','active')->count() }}/{{ \App\Models\Sensor::count() }} sensor online</p>
</div>
</div>
</div>

<div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-blue-200">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="radio" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-blue-800 text-sm sm:text-base">Komunikasi LoRa</p>
<p class="text-blue-600 text-xs sm:text-sm">Signal: {{ $device?->signal_strength ?? '-' }}%</p>
</div>
</div>
</div>

<div class="bg-gradient-to-br from-purple-50 to-pink-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-purple-200">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="clock" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800 text-sm sm:text-base">Update terakhir</p>
<p class="text-purple-600 text-xs sm:text-sm">{{ $lastUpdate ? $lastUpdate->diffForHumans() : 'Belum ada data' }}</p>
</div>
</div>
</div>
</div>
</div>

</main>
</div>
</div>

<script>
feather.replace();

// Toggle sidebar mobile
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

// =============================================
// POLLING REALTIME — update kartu sensor tanpa reload halaman
// =============================================
const DETECTION_WINDOW = 30; // detik

async function pollSensorData() {
  try {
    const [pirRes, vibRes, reedRes] = await Promise.all([
      fetch('/api/pir/readings?limit=1').then(r => r.json()).catch(() => null),
      fetch('/api/vibration/readings?limit=1').then(r => r.json()).catch(() => null),
      fetch('/api/door-access/readings?limit=1').then(r => r.json()).catch(() => null),
    ]);

    const now = Date.now();

    // PIR
    if (pirRes?.success && pirRes.data?.length > 0) {
      const d = pirRes.data[0];
      const age = (now - new Date(d.recorded_at).getTime()) / 1000;
      const active = d.motion_detected && age <= DETECTION_WINDOW;
      document.getElementById('pir-status-text').textContent = active ? '⚠ Gerakan' : '✓ Aman';
      document.getElementById('pir-status-text').className = 'text-xl md:text-3xl font-display font-bold mb-1 ' + (active ? 'text-red-600' : 'text-green-600');
      document.getElementById('pir-detail').textContent = 'Intensitas: ' + (d.motion_intensity ?? 0) + '% · Durasi: ' + (d.duration_seconds ?? 0) + 's';
      document.getElementById('pir-update').textContent = 'Baru saja';
      document.getElementById('pir-update').className = active ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold';
    }

    // Vibration
    if (vibRes?.success && vibRes.data?.length > 0) {
      const d = vibRes.data[0];
      const age = (now - new Date(d.recorded_at).getTime()) / 1000;
      const active = d.is_abnormal && age <= DETECTION_WINDOW;
      document.getElementById('vib-status-text').textContent = active ? '⚠ Abnormal' : '✓ Stabil';
      document.getElementById('vib-status-text').className = 'text-xl md:text-3xl font-display font-bold mb-1 ' + (active ? 'text-red-600' : 'text-green-600');
      document.getElementById('vib-update').textContent = 'Baru saja';
      document.getElementById('vib-update').className = active ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold';
    }

    // Reed Switch
    if (reedRes?.success && reedRes.data?.length > 0) {
      const d = reedRes.data[0];
      const age = (now - new Date(d.recorded_at).getTime()) / 1000;
      const active = d.door_open && age <= DETECTION_WINDOW;
      document.getElementById('reed-status-text').textContent = active ? '⚠ Terbuka' : '✓ Tertutup';
      document.getElementById('reed-status-text').className = 'text-xl md:text-3xl font-display font-bold mb-1 ' + (active ? 'text-red-600' : 'text-green-600');
      document.getElementById('reed-update').textContent = 'Baru saja';
      document.getElementById('reed-update').className = active ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold';
    }

    // Update dot indicator
    const dot = document.getElementById('polling-dot');
    dot.classList.remove('bg-red-500');
    dot.classList.add('bg-green-500');

  } catch (err) {
    console.error('Polling error:', err);
    const dot = document.getElementById('polling-dot');
    dot.classList.remove('bg-green-500');
    dot.classList.add('bg-red-500');
  }
}

// Poll setiap 10 detik
setInterval(pollSensorData, 10000);
</script>

</body>
</html>
