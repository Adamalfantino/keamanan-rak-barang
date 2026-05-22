<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log Aktivitas - Smart Rack Security</title>
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

<a href="/log" class="group flex items-center gap-4 px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg">
<div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
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
<h1 class="text-lg md:text-2xl font-display font-bold text-gray-800">Log Aktivitas</h1>
<p class="text-gray-600 mt-0.5 text-xs md:text-sm hidden sm:block">Riwayat kejadian dan aktivitas sensor</p>
</div>
</div>
<div class="flex items-center gap-2 md:gap-4">
<div class="flex items-center gap-2 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 px-3 py-2 rounded-xl border border-blue-200">
<div class="w-2 h-2 md:w-3 md:h-3 bg-blue-500 rounded-full animate-pulse-slow"></div>
<span class="text-blue-700 font-semibold text-xs md:text-sm hidden sm:inline">Live Updates</span>
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


<!-- LOG TABLE -->
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg overflow-hidden border border-gray-100">

<div class="p-4 sm:p-6 md:p-8 border-b border-gray-100">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
<div class="flex items-center gap-3">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="list" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">Riwayat Aktivitas</h3>
<p class="text-gray-600 text-xs sm:text-sm">Total {{ $totalLog }} log tercatat</p>
</div>
</div>
<div class="flex gap-2 flex-wrap">
<span class="px-2.5 py-1 bg-blue-100 text-blue-700 rounded-xl text-xs sm:text-sm font-semibold">Hari ini: {{ $logHariIni }}</span>
<span class="px-2.5 py-1 bg-yellow-100 text-yellow-700 rounded-xl text-xs sm:text-sm font-semibold">Warning: {{ $logWarning }}</span>
<span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-xl text-xs sm:text-sm font-semibold">Critical: {{ $logCritical }}</span>
</div>
</div>
</div>

<div class="overflow-x-auto hidden md:block">
<table class="w-full">
<thead class="bg-gradient-to-r from-slate-50 to-gray-50 border-b border-gray-200">
<tr>
<th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Waktu</th>
<th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Sensor</th>
<th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status</th>
<th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Detail</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-100">

@forelse($logs as $log)
@php
$severityColor = match($log->severity) {
    'warning'  => ['row' => 'hover:from-yellow-50 hover:to-orange-50', 'icon' => 'bg-yellow-100', 'text' => 'text-yellow-600', 'badge' => 'from-yellow-500/10 to-orange-500/10 text-yellow-700 border-yellow-200', 'dot' => 'bg-yellow-500'],
    'critical' => ['row' => 'hover:from-red-50 hover:to-pink-50',      'icon' => 'bg-red-100',    'text' => 'text-red-600',    'badge' => 'from-red-500/10 to-pink-500/10 text-red-700 border-red-200',       'dot' => 'bg-red-500'],
    default    => ['row' => 'hover:from-green-50 hover:to-emerald-50', 'icon' => 'bg-green-100',  'text' => 'text-green-600',  'badge' => 'from-green-500/10 to-emerald-500/10 text-green-700 border-green-200', 'dot' => 'bg-green-500'],
};
$sensorIcon = match($log->sensor?->type ?? '') {
    'pir'         => 'activity',
    'vibration'   => 'zap',
    'reed_switch' => 'unlock',
    default       => 'cpu',
};
@endphp
<tr class="group hover:bg-gradient-to-r {{ $severityColor['row'] }} transition-all duration-300">
<td class="px-6 py-5">
<div class="flex items-center gap-3">
<div class="w-10 h-10 {{ $severityColor['icon'] }} rounded-xl flex items-center justify-center">
<i data-feather="clock" class="w-5 h-5 {{ $severityColor['text'] }}"></i>
</div>
<div>
<p class="font-semibold text-gray-800">{{ $log->event_time->format('H:i') }}</p>
<p class="text-sm text-gray-500">{{ $log->event_time->format('d M Y') }}</p>
</div>
</div>
</td>
<td class="px-6 py-5">
<div class="flex items-center gap-3">
<div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
<i data-feather="{{ $sensorIcon }}" class="w-5 h-5 text-indigo-600"></i>
</div>
<div>
<p class="font-semibold text-gray-800">{{ $log->sensor?->name ?? $log->device?->name ?? 'Sistem' }}</p>
<p class="text-sm text-gray-500">{{ $log->location ?? '-' }}</p>
</div>
</div>
</td>
<td class="px-6 py-5">
<span class="inline-flex items-center gap-2 bg-gradient-to-r {{ $severityColor['badge'] }} px-4 py-2 rounded-xl text-sm font-semibold border">
<div class="w-2 h-2 {{ $severityColor['dot'] }} rounded-full"></div>
{{ $log->event_type_display }}
</span>
</td>
<td class="px-6 py-5">
<p class="text-gray-600 text-sm">{{ $log->description }}</p>
</td>
</tr>
@empty
<tr>
<td colspan="4" class="px-8 py-16 text-center">
<div class="flex flex-col items-center gap-3">
<div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center">
<i data-feather="inbox" class="w-8 h-8 text-gray-400"></i>
</div>
<p class="text-gray-500 font-medium">Belum ada log aktivitas</p>
<p class="text-gray-400 text-sm">Log akan muncul saat sensor mendeteksi aktivitas</p>
</div>
</td>
</tr>
@endforelse

</tbody>
</table>
</div>

<!-- CARD VIEW untuk mobile -->
<div class="md:hidden divide-y divide-gray-100">
@forelse($logs as $log)
@php
$severityColor = match($log->severity) {
    'warning'  => ['bg' => 'bg-yellow-50 border-yellow-200', 'badge' => 'bg-yellow-100 text-yellow-700', 'dot' => 'bg-yellow-500'],
    'critical' => ['bg' => 'bg-red-50 border-red-200',       'badge' => 'bg-red-100 text-red-700',       'dot' => 'bg-red-500'],
    default    => ['bg' => 'bg-green-50 border-green-200',   'badge' => 'bg-green-100 text-green-700',   'dot' => 'bg-green-500'],
};
$sensorIcon = match($log->sensor?->type ?? '') {
    'pir'         => 'activity',
    'vibration'   => 'zap',
    'reed_switch' => 'unlock',
    default       => 'cpu',
};
@endphp
<div class="p-4 {{ $severityColor['bg'] }} border-l-4">
<div class="flex items-start justify-between gap-3 mb-2">
<div class="flex items-center gap-2">
<div class="w-2 h-2 {{ $severityColor['dot'] }} rounded-full mt-1 flex-shrink-0"></div>
<p class="font-semibold text-gray-800 text-sm">{{ $log->event_type_display }}</p>
</div>
<span class="text-xs {{ $severityColor['badge'] }} px-2 py-1 rounded-lg font-semibold flex-shrink-0">
{{ $log->event_time->format('H:i') }}
</span>
</div>
<p class="text-gray-600 text-xs mb-1">{{ $log->description }}</p>
<p class="text-gray-400 text-xs">
{{ $log->sensor?->name ?? $log->device?->name ?? 'Sistem' }} · {{ $log->event_time->format('d M Y') }}
</p>
</div>
@empty
<div class="p-8 text-center">
<i data-feather="inbox" class="w-8 h-8 text-gray-400 mx-auto mb-2"></i>
<p class="text-gray-500 text-sm">Belum ada log aktivitas</p>
</div>
@endforelse
</div>

<!-- Pagination -->
@if($logs->hasPages())
<div class="p-6 border-t border-gray-100">
{{ $logs->links() }}
</div>
@endif

</div>


<!-- STATUS INFO -->
<div class="bg-white/80 backdrop-blur-sm rounded-2xl md:rounded-3xl shadow-lg p-4 sm:p-6 md:p-8 border border-gray-100">
<div class="flex items-center gap-3 mb-5 sm:mb-8">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
<i data-feather="info" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<h3 class="text-lg sm:text-xl font-display font-bold text-gray-800">Informasi Sistem</h3>
<p class="text-gray-600 text-xs sm:text-sm">Status operasional logging</p>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-4 md:gap-6">
<div class="group bg-gradient-to-br from-green-50 to-emerald-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-green-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
<i data-feather="check-circle" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800 text-sm sm:text-base">Sensor aktif dan berjalan normal</p>
<p class="text-green-600 text-xs sm:text-sm">Semua sistem operasional</p>
</div>
</div>
</div>
<div class="group bg-gradient-to-br from-blue-50 to-indigo-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-blue-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
<i data-feather="radio" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-blue-800 text-sm sm:text-base">Komunikasi LoRa stabil</p>
<p class="text-blue-600 text-xs sm:text-sm">Koneksi optimal</p>
</div>
</div>
</div>
<div class="group bg-gradient-to-br from-purple-50 to-pink-50 p-4 sm:p-5 md:p-6 rounded-xl sm:rounded-2xl border border-purple-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-3 sm:gap-4">
<div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-500 rounded-lg sm:rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
<i data-feather="clock" class="w-5 h-5 sm:w-6 sm:h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800 text-sm sm:text-base">Update log terakhir</p>
<p class="text-purple-600 text-xs sm:text-sm">{{ $logs->first()?->event_time->diffForHumans() ?? 'Belum ada data' }}</p>
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