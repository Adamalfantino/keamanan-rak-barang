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

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen p-4 md:p-6 font-sans overflow-x-hidden">

<!-- HEADER -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
<div class="flex items-center gap-3">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="activity" class="w-6 h-6 text-white"></i>
</div>
<div>
<h1 class="text-2xl md:text-4xl font-display font-bold text-gray-800">Monitoring Sensor</h1>
<p class="text-gray-600 mt-0.5 text-sm">Data realtime dari sensor keamanan rak</p>
</div>
</div>

<div class="flex items-center gap-3 w-full sm:w-auto">
<div class="flex items-center gap-2 bg-gradient-to-r from-green-500/10 to-emerald-500/10 px-3 py-2 rounded-xl border border-green-200">
<div class="w-2 h-2 bg-green-500 rounded-full animate-pulse-slow"></div>
<span class="text-green-700 font-semibold text-xs">Live Monitoring</span>
</div>
<a href="/dashboard" class="ml-auto sm:ml-0 group bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-4 py-2 md:px-6 md:py-3 rounded-xl font-semibold hover:shadow-lg hover:scale-105 transition-all duration-300 flex items-center gap-2">
<i data-feather="arrow-left" class="w-4 h-4 group-hover:-translate-x-1 transition-transform"></i>
<span class="text-sm md:text-base">Dashboard</span>
</a>
</div>
</div>

<!-- STATISTIK 24 JAM -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
<div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex items-center gap-4">
<div class="w-14 h-14 bg-indigo-100 rounded-xl flex items-center justify-center">
<i data-feather="eye" class="w-7 h-7 text-indigo-600"></i>
</div>
<div>
<p class="text-gray-500 text-sm">Gerakan Terdeteksi (24 jam)</p>
<p class="text-3xl font-display font-bold text-indigo-600">{{ $pirCount24h }}</p>
</div>
</div>
<div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex items-center gap-4">
<div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center">
<i data-feather="zap" class="w-7 h-7 text-yellow-600"></i>
</div>
<div>
<p class="text-gray-500 text-sm">Getaran Abnormal (24 jam)</p>
<p class="text-3xl font-display font-bold text-yellow-600">{{ $vibrationCount24h }}</p>
</div>
</div>
<div class="bg-white rounded-2xl shadow p-6 border border-gray-100 flex items-center gap-4">
<div class="w-14 h-14 bg-purple-100 rounded-xl flex items-center justify-center">
<i data-feather="unlock" class="w-7 h-7 text-purple-600"></i>
</div>
<div>
<p class="text-gray-500 text-sm">Rak Dibuka (24 jam)</p>
<p class="text-3xl font-display font-bold text-purple-600">{{ $reedCount24h }}</p>
</div>
</div>
</div>

<!-- SENSOR GRID -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-10">

<!-- PIR -->
<div class="group bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-blue-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-6">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="eye" class="w-8 h-8 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Sensor PIR</h3>
<p class="text-gray-500 text-sm">Motion Detection</p>
</div>
</div>
@if($pirSensor && $pirSensor->status === 'active')
<span class="bg-green-100 text-green-700 px-3 py-1 rounded-xl text-xs font-semibold border border-green-200">Aktif</span>
@else
<span class="bg-red-100 text-red-700 px-3 py-1 rounded-xl text-xs font-semibold border border-red-200">Tidak Aktif</span>
@endif
</div>

@if($pirLatest)
<div class="mb-4">
<p class="text-3xl font-display font-bold {{ $pirLatest->motion_detected ? 'text-red-600' : 'text-green-600' }} mb-1">
{{ $pirLatest->motion_detected ? '⚠ Gerakan Terdeteksi' : '✓ Tidak Ada Gerakan' }}
</p>
<p class="text-gray-500 text-sm">
Tipe: <span class="font-semibold text-gray-700">{{ ucfirst($pirLatest->motion_type ?? '-') }}</span>
&nbsp;|&nbsp; Intensitas: <span class="font-semibold text-gray-700">{{ $pirLatest->motion_intensity ?? 0 }}%</span>
</p>
<p class="text-gray-500 text-sm mt-1">
Zona: <span class="font-semibold text-gray-700">{{ ucfirst($pirLatest->detection_zone ?? '-') }}</span>
&nbsp;|&nbsp; Durasi: <span class="font-semibold text-gray-700">{{ $pirLatest->duration_seconds ?? 0 }}s</span>
</p>
</div>
<div class="pt-4 border-t border-gray-100 flex justify-between text-sm">
<span class="text-gray-500">Update terakhir:</span>
<span class="{{ $pirLatest->motion_detected ? 'text-red-600' : 'text-green-600' }} font-semibold">
{{ $pirLatest->recorded_at->diffForHumans() }}
</span>
</div>
@else
<p class="text-2xl font-display font-bold text-gray-400 mb-2">Belum Ada Data</p>
<p class="text-gray-500 text-sm">Menunggu data dari sensor...</p>
@endif
</div>
</div>

<!-- VIBRATION -->
<div class="group bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-yellow-50 to-orange-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-6">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="zap" class="w-8 h-8 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Sensor Getar</h3>
<p class="text-gray-500 text-sm">SW-420 Vibration</p>
</div>
</div>
@if($vibrationSensor && $vibrationSensor->status === 'active')
<span class="bg-green-100 text-green-700 px-3 py-1 rounded-xl text-xs font-semibold border border-green-200">Aktif</span>
@else
<span class="bg-red-100 text-red-700 px-3 py-1 rounded-xl text-xs font-semibold border border-red-200">Tidak Aktif</span>
@endif
</div>

@if($vibrationLatest)
<div class="mb-4">
<p class="text-3xl font-display font-bold {{ $vibrationLatest->is_abnormal ? 'text-red-600' : 'text-green-600' }} mb-1">
{{ $vibrationLatest->is_abnormal ? '⚠ Getaran Abnormal' : '✓ Stabil' }}
</p>
<p class="text-gray-500 text-sm">
Status: <span class="font-semibold text-gray-700">{{ ucfirst($vibrationLatest->status ?? '-') }}</span>
&nbsp;|&nbsp; Magnitude: <span class="font-semibold text-gray-700">{{ number_format($vibrationLatest->magnitude ?? 0, 2) }}</span>
</p>
<p class="text-gray-500 text-sm mt-1">
X: <span class="font-semibold">{{ number_format($vibrationLatest->x_axis, 2) }}</span>
&nbsp; Y: <span class="font-semibold">{{ number_format($vibrationLatest->y_axis, 2) }}</span>
&nbsp; Z: <span class="font-semibold">{{ number_format($vibrationLatest->z_axis, 2) }}</span>
</p>
</div>
<div class="pt-4 border-t border-gray-100 flex justify-between text-sm">
<span class="text-gray-500">Update terakhir:</span>
<span class="{{ $vibrationLatest->is_abnormal ? 'text-red-600' : 'text-green-600' }} font-semibold">
{{ $vibrationLatest->recorded_at->diffForHumans() }}
</span>
</div>
@else
<p class="text-2xl font-display font-bold text-gray-400 mb-2">Belum Ada Data</p>
<p class="text-gray-500 text-sm">Menunggu data dari sensor...</p>
@endif
</div>
</div>

<!-- REED SWITCH -->
<div class="group bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-500 p-8 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
<div class="relative z-10">
<div class="flex items-center justify-between mb-6">
<div class="flex items-center gap-4">
<div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg">
<i data-feather="unlock" class="w-8 h-8 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Reed Switch</h3>
<p class="text-gray-500 text-sm">Door/Lock Status</p>
</div>
</div>
@if($reedSensor && $reedSensor->status === 'active')
<span class="bg-green-100 text-green-700 px-3 py-1 rounded-xl text-xs font-semibold border border-green-200">Aktif</span>
@else
<span class="bg-red-100 text-red-700 px-3 py-1 rounded-xl text-xs font-semibold border border-red-200">Tidak Aktif</span>
@endif
</div>

@if($reedLatest)
<div class="mb-4">
<p class="text-3xl font-display font-bold {{ $reedLatest->door_open ? 'text-red-600' : 'text-green-600' }} mb-1">
{{ $reedLatest->door_open ? '⚠ Rak Terbuka' : '✓ Rak Tertutup' }}
</p>
<p class="text-gray-500 text-sm">
Status: <span class="font-semibold text-gray-700">{{ ucfirst($reedLatest->door_status ?? '-') }}</span>
&nbsp;|&nbsp; Level: <span class="font-semibold text-gray-700">{{ ucfirst($reedLatest->access_level ?? '-') }}</span>
</p>
<p class="text-gray-500 text-sm mt-1">
Metode: <span class="font-semibold text-gray-700">{{ ucfirst($reedLatest->access_method ?? '-') }}</span>
&nbsp;|&nbsp; Durasi: <span class="font-semibold text-gray-700">{{ $reedLatest->open_duration_seconds ?? 0 }}s</span>
</p>
</div>
<div class="pt-4 border-t border-gray-100 flex justify-between text-sm">
<span class="text-gray-500">Update terakhir:</span>
<span class="{{ $reedLatest->door_open ? 'text-red-600' : 'text-green-600' }} font-semibold">
{{ $reedLatest->recorded_at->diffForHumans() }}
</span>
</div>
@else
<p class="text-2xl font-display font-bold text-gray-400 mb-2">Belum Ada Data</p>
<p class="text-gray-500 text-sm">Menunggu data dari sensor...</p>
@endif
</div>
</div>

</div>

<!-- RIWAYAT DATA TERBARU -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">

<!-- Riwayat PIR -->
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-6 border border-gray-100">
<div class="flex items-center gap-3 mb-5">
<div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
<i data-feather="eye" class="w-5 h-5 text-indigo-600"></i>
</div>
<h4 class="font-display font-bold text-gray-800">Riwayat PIR</h4>
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
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-6 border border-gray-100">
<div class="flex items-center gap-3 mb-5">
<div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
<i data-feather="zap" class="w-5 h-5 text-yellow-600"></i>
</div>
<h4 class="font-display font-bold text-gray-800">Riwayat Getaran</h4>
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
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-6 border border-gray-100">
<div class="flex items-center gap-3 mb-5">
<div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
<i data-feather="unlock" class="w-5 h-5 text-purple-600"></i>
</div>
<h4 class="font-display font-bold text-gray-800">Riwayat Reed Switch</h4>
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
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-100">
<div class="flex items-center gap-3 mb-6">
<div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
<i data-feather="monitor" class="w-6 h-6 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Status Sistem</h3>
<p class="text-gray-600">Kondisi operasional monitoring</p>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
<div class="bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-200">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
<i data-feather="check-circle" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800">Sensor aktif</p>
<p class="text-green-600 text-sm">{{ \App\Models\Sensor::where('status','active')->count() }}/{{ \App\Models\Sensor::count() }} sensor online</p>
</div>
</div>
</div>

<div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-6 rounded-2xl border border-blue-200">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
<i data-feather="radio" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-blue-800">Komunikasi LoRa</p>
<p class="text-blue-600 text-sm">Signal: {{ $device?->signal_strength ?? '-' }}%</p>
</div>
</div>
</div>

<div class="bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-2xl border border-purple-200">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center">
<i data-feather="clock" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800">Update terakhir</p>
<p class="text-purple-600 text-sm">{{ $lastUpdate ? $lastUpdate->diffForHumans() : 'Belum ada data' }}</p>
</div>
</div>
</div>
</div>
</div>

<script>
feather.replace();

// Auto refresh setiap 10 detik
setTimeout(function() {
    window.location.reload();
}, 10000);
</script>

</body>
</html>
