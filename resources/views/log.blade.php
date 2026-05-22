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

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen p-4 md:p-6 font-sans overflow-x-hidden">

<!-- HEADER -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">

<div>
<div class="flex items-center gap-3 mb-2">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="file-text" class="w-6 h-6 text-white"></i>
</div>
<div>
<h1 class="text-2xl md:text-4xl font-display font-bold text-gray-800">Log Aktivitas</h1>
<p class="text-gray-600 mt-0.5 text-sm">Riwayat kejadian dan aktivitas sensor</p>
</div>
</div>
</div>

<div class="flex items-center gap-3 w-full sm:w-auto">
<div class="flex items-center gap-2 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 px-3 py-2 rounded-xl border border-blue-200">
<div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse-slow"></div>
<span class="text-blue-700 font-semibold text-xs">Live Updates</span>
</div>

<a href="/dashboard" class="ml-auto sm:ml-0 group bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-xl font-semibold hover:shadow-lg hover:scale-105 transition-all duration-300 flex items-center gap-2">
<i data-feather="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
<span class="text-sm md:text-base">Dashboard</span>
</a>
</div>

</div>


<!-- LOG TABLE -->
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg overflow-hidden border border-gray-100">

<div class="p-8 border-b border-gray-100">
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
<div class="flex items-center gap-3">
<div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
<i data-feather="list" class="w-6 h-6 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Riwayat Aktivitas</h3>
<p class="text-gray-600">Total {{ $totalLog }} log tercatat</p>
</div>
</div>
<!-- Statistik ringkas -->
<div class="flex gap-3 flex-wrap">
<span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-xl text-sm font-semibold">Hari ini: {{ $logHariIni }}</span>
<span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-xl text-sm font-semibold">Warning: {{ $logWarning }}</span>
<span class="px-3 py-1 bg-red-100 text-red-700 rounded-xl text-sm font-semibold">Critical: {{ $logCritical }}</span>
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
<div class="mt-12 bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-100">

<div class="flex items-center gap-3 mb-8">
<div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
<i data-feather="info" class="w-6 h-6 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Informasi Sistem</h3>
<p class="text-gray-600">Status operasional logging</p>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<div class="group bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="check-circle" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800">Sensor aktif dan berjalan normal</p>
<p class="text-green-600 text-sm">Semua sistem operasional</p>
</div>
</div>
</div>

<div class="group bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="radio" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-blue-800">Komunikasi LoRa stabil</p>
<p class="text-blue-600 text-sm">Koneksi optimal</p>
</div>
</div>
</div>

<div class="group bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-2xl border border-purple-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="clock" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800">Update log terakhir</p>
<p class="text-purple-600 text-sm">{{ $logs->first()?->event_time->diffForHumans() ?? 'Belum ada data' }}</p>
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