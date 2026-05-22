<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Smart Rack Security</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .glass { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.9); border: 1px solid rgba(255, 255, 255, 0.2); }
  .animate-pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
  .animate-bounce-slow { animation: bounce 2s infinite; }

  /* LED & Buzzer Animations */
  @keyframes led-blink {
    0%, 100% { opacity: 1; box-shadow: 0 0 12px 4px currentColor; }
    50% { opacity: 0.3; box-shadow: none; }
  }
  @keyframes buzzer-wave {
    0% { transform: scale(1); opacity: 1; }
    100% { transform: scale(2.5); opacity: 0; }
  }
  .led-on-red {
    animation: led-blink 0.6s ease-in-out infinite;
    color: #ef4444;
  }
  .led-on-green {
    box-shadow: 0 0 12px 4px #22c55e;
    color: #22c55e;
  }
  .led-off {
    color: #6b7280;
    opacity: 0.4;
  }
  .buzzer-active::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid #ef4444;
    animation: buzzer-wave 1s ease-out infinite;
  }
  .buzzer-active::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 3px solid #ef4444;
    animation: buzzer-wave 1s ease-out 0.4s infinite;
  }
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

<a href="/dashboard" class="group flex items-center gap-4 px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg">
<div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
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
<img src="https://i.pravatar.cc/48" class="w-12 h-12 rounded-xl shadow-lg">
<div class="absolute -bottom-1 -right-1 w-4 h-4 bg-green-500 rounded-full border-2 border-slate-800"></div>
</div>

<div class="flex-1">
<p class="font-semibold text-white">Admin</p>
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
<!-- Hamburger button mobile -->
<button onclick="toggleSidebar()" class="md:hidden w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center text-gray-600 hover:bg-gray-200 transition-all">
<i data-feather="menu" class="w-5 h-5"></i>
</button>
<div>
<h1 class="text-lg md:text-2xl font-display font-bold text-gray-800">Dashboard Monitoring</h1>
<p class="text-gray-600 mt-0.5 text-xs md:text-sm hidden sm:block">Sistem keamanan rak berbasis IoT</p>
</div>
</div>

<div class="flex items-center gap-2 md:gap-6">
<div class="flex items-center gap-2 bg-gradient-to-r from-green-500/10 to-emerald-500/10 px-3 py-2 rounded-xl border border-green-200">
<div class="w-2 h-2 md:w-3 md:h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
<span class="text-green-700 font-semibold text-xs md:text-sm hidden sm:inline">Sistem Online</span>
</div>

<div class="flex items-center gap-2">
<div class="relative">
<img src="https://i.pravatar.cc/44" class="w-9 h-9 md:w-11 md:h-11 rounded-xl shadow-lg">
<div class="absolute -bottom-1 -right-1 w-3 h-3 md:w-4 md:h-4 bg-green-500 rounded-full border-2 border-white"></div>
</div>
<div class="hidden sm:block">
<span class="text-gray-800 font-semibold text-sm">Admin</span>
<p class="text-gray-500 text-xs">Online</p>
</div>
</div>
</div>
</header>

<!-- CONTENT -->
<main class="p-4 md:p-8 space-y-6 md:space-y-10 overflow-y-auto">

<div class="mb-4 md:mb-8">
<h2 class="text-2xl md:text-3xl font-display font-bold text-gray-800 mb-1 md:mb-2">Ringkasan Sistem</h2>
<p class="text-gray-600 text-sm md:text-base">Overview monitoring keamanan rak secara realtime</p>
</div>

<!-- STATISTICS -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-6">

<div class="group bg-white p-4 md:p-8 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-10 h-10 md:w-16 md:h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 md:mb-4">
<i data-feather="layers" class="w-5 h-5 md:w-8 md:h-8 text-white"></i>
</div>
<p class="text-gray-600 font-medium mb-1 md:mb-2 text-xs md:text-base">Total Sensor</p>
<h3 class="text-2xl md:text-4xl font-display font-bold text-indigo-600">{{ $totalSensor }}</h3>
</div>
</div>

<div class="group bg-white p-4 md:p-8 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-emerald-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-10 h-10 md:w-16 md:h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 md:mb-4">
<i data-feather="check-circle" class="w-5 h-5 md:w-8 md:h-8 text-white"></i>
</div>
<p class="text-gray-600 font-medium mb-1 md:mb-2 text-xs md:text-base">Sensor Aktif</p>
<h3 class="text-2xl md:text-4xl font-display font-bold text-green-600">{{ $sensorAktif }}</h3>
</div>
</div>

<div class="group bg-white p-4 md:p-8 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-pink-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-10 h-10 md:w-16 md:h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 md:mb-4">
<i data-feather="alert-triangle" class="w-5 h-5 md:w-8 md:h-8 text-white"></i>
</div>
<p class="text-gray-600 font-medium mb-1 md:mb-2 text-xs md:text-base">Peringatan</p>
<h3 class="text-2xl md:text-4xl font-display font-bold text-red-600">{{ $peringatanHariIni }}</h3>
</div>
</div>

<div class="group bg-white p-4 md:p-8 rounded-2xl md:rounded-3xl shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 border border-gray-100 relative overflow-hidden">
<div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-10 h-10 md:w-16 md:h-16 bg-gradient-to-br from-purple-500 to-blue-600 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 md:mb-4">
<i data-feather="shield-check" class="w-5 h-5 md:w-8 md:h-8 text-white"></i>
</div>
<p class="text-gray-600 font-medium mb-1 md:mb-2 text-xs md:text-base">Status</p>
<h3 class="text-xl md:text-3xl font-display font-bold text-purple-600">{{ $alertAktif > 0 ? 'Waspada' : 'Normal' }}</h3>
</div>
</div>

</div>

<!-- ALERT REALTIME -->
@if($alertTerbaru->count() > 0 || $pirAktif || $vibAktif || $reedAktif)
<div class="bg-gradient-to-br from-red-50 to-orange-50 rounded-3xl shadow-lg p-8 border border-red-200">
  <div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center">
        <i data-feather="alert-triangle" class="w-6 h-6 text-white"></i>
      </div>
      <div>
        <h3 class="text-xl font-display font-bold text-red-800">⚠ Alert Aktif</h3>
        <p class="text-red-600 text-sm">{{ $alertTerbaru->count() }} peringatan memerlukan perhatian</p>
      </div>
    </div>
    <a href="/log" class="text-red-600 hover:text-red-800 text-sm font-semibold flex items-center gap-1">
      Lihat semua <i data-feather="arrow-right" class="w-4 h-4"></i>
    </a>
  </div>

  <!-- Deteksi sensor aktif saat ini -->
  @if($pirAktif || $vibAktif || $reedAktif)
  <div class="mb-4 p-4 bg-red-500 rounded-2xl text-white flex items-center gap-3">
    <div class="w-3 h-3 bg-white rounded-full animate-pulse flex-shrink-0"></div>
    <p class="font-semibold text-sm">
      DETEKSI AKTIF SEKARANG:
      @if($pirAktif) <span class="bg-white/20 px-2 py-0.5 rounded-lg ml-1">PIR</span> @endif
      @if($vibAktif) <span class="bg-white/20 px-2 py-0.5 rounded-lg ml-1">SW-420</span> @endif
      @if($reedAktif) <span class="bg-white/20 px-2 py-0.5 rounded-lg ml-1">Reed Switch</span> @endif
    </p>
  </div>
  @endif

  <!-- Daftar alert -->
  <div class="space-y-3">
    @foreach($alertTerbaru as $alert)
    @php
      $priorityColor = match($alert->priority) {
        'critical' => 'bg-red-100 border-red-300 text-red-800',
        'high'     => 'bg-orange-100 border-orange-300 text-orange-800',
        'medium'   => 'bg-yellow-100 border-yellow-300 text-yellow-800',
        default    => 'bg-blue-100 border-blue-300 text-blue-800',
      };
      $dotColor = match($alert->priority) {
        'critical' => 'bg-red-500',
        'high'     => 'bg-orange-500',
        'medium'   => 'bg-yellow-500',
        default    => 'bg-blue-500',
      };
    @endphp
    <div class="flex items-start gap-4 p-4 {{ $priorityColor }} rounded-2xl border">
      <div class="w-3 h-3 {{ $dotColor }} rounded-full mt-1 flex-shrink-0 animate-pulse"></div>
      <div class="flex-1">
        <p class="font-semibold text-sm">{{ $alert->title }}</p>
        <p class="text-xs mt-0.5 opacity-80">{{ $alert->message }}</p>
        <p class="text-xs mt-1 opacity-60">
          {{ $alert->device?->name ?? 'Sistem' }} &nbsp;·&nbsp;
          {{ $alert->triggered_at?->diffForHumans() ?? '-' }}
        </p>
      </div>
      <span class="text-xs font-bold uppercase px-2 py-1 bg-white/50 rounded-lg flex-shrink-0">
        {{ $alert->priority }}
      </span>
    </div>
    @endforeach
  </div>
</div>
@endif

<!-- BUZZER & LED STATUS -->
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-100">

  <div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-3">
      <div class="w-12 h-12 bg-gradient-to-br from-red-500 to-orange-600 rounded-xl flex items-center justify-center">
        <i data-feather="bell" class="w-6 h-6 text-white"></i>
      </div>
      <div>
        <h3 class="text-xl font-display font-bold text-gray-800">Status Buzzer & LED Lokal</h3>
        <p class="text-gray-600 text-sm">Indikator perangkat keras saat sensor terdeteksi</p>
      </div>
    </div>
    <div class="flex items-center gap-2 text-xs text-gray-400">
      <div id="polling-dot" class="w-2 h-2 bg-green-400 rounded-full animate-pulse-slow"></div>
      <span id="last-update-text">Memuat...</span>
    </div>
  </div>

  <!-- ALERT BANNER -->
  <div id="alert-banner" class="hidden mb-6 p-4 bg-gradient-to-r from-red-500 to-orange-500 rounded-2xl text-white flex items-center gap-4 shadow-lg">
    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
      <i data-feather="alert-triangle" class="w-5 h-5"></i>
    </div>
    <div class="flex-1">
      <p class="font-bold text-sm">⚠ PERINGATAN AKTIF</p>
      <p id="alert-banner-text" class="text-red-100 text-xs mt-0.5">Sensor mendeteksi aktivitas mencurigakan</p>
    </div>
    <button onclick="dismissAlert()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-all">
      <i data-feather="x" class="w-4 h-4"></i>
    </button>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-8">

    <!-- BUZZER -->
    <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-2xl p-6 border border-gray-200">
      <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-5">Buzzer</p>
      <div class="flex items-center gap-6">
        <!-- Buzzer Icon -->
        <div class="relative flex items-center justify-center w-20 h-20 flex-shrink-0">
          <div id="buzzer-icon" class="relative w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center transition-all duration-300">
            <i data-feather="volume-x" class="w-7 h-7 text-gray-400" id="buzzer-icon-inner"></i>
          </div>
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <div id="buzzer-status-dot" class="w-4 h-4 rounded-full bg-gray-300 transition-all duration-300"></div>
            <p id="buzzer-status-text" class="text-xl font-display font-bold text-gray-400">Tidak Aktif</p>
          </div>
          <p id="buzzer-trigger-text" class="text-gray-500 text-sm">Menunggu deteksi sensor...</p>
          <div class="mt-3 flex flex-wrap gap-2" id="buzzer-triggers">
            <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded-lg">PIR</span>
            <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded-lg">SW-420</span>
            <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded-lg">Reed Switch</span>
          </div>
        </div>
      </div>
    </div>

    <!-- LED -->
    <div class="bg-gradient-to-br from-gray-50 to-slate-100 rounded-2xl p-6 border border-gray-200">
      <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-5">LED Indikator</p>
      <div class="flex items-center gap-6">
        <!-- LED Icon -->
        <div class="flex flex-col items-center gap-3 flex-shrink-0">
          <!-- LED Merah -->
          <div class="flex items-center gap-2">
            <div id="led-red" class="w-8 h-8 rounded-full bg-gray-200 led-off transition-all duration-300 flex items-center justify-center">
              <div class="w-4 h-4 rounded-full bg-gray-400"></div>
            </div>
            <span class="text-xs text-gray-500 font-medium">MERAH</span>
          </div>
          <!-- LED Hijau -->
          <div class="flex items-center gap-2">
            <div id="led-green" class="w-8 h-8 rounded-full bg-green-100 transition-all duration-300 flex items-center justify-center" style="box-shadow: 0 0 10px 3px #22c55e;">
              <div class="w-4 h-4 rounded-full bg-green-500"></div>
            </div>
            <span class="text-xs text-gray-500 font-medium">HIJAU</span>
          </div>
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-3 mb-2">
            <p id="led-status-text" class="text-xl font-display font-bold text-green-600">Aman</p>
          </div>
          <p id="led-detail-text" class="text-gray-500 text-sm">LED hijau menyala — sistem normal</p>
          <div class="mt-3 p-2 bg-white rounded-xl border border-gray-100">
            <div class="flex items-center gap-2 text-xs text-gray-500">
              <div class="w-3 h-3 rounded-full bg-green-400"></div>
              <span>Hijau = Aman &nbsp;|&nbsp;</span>
              <div class="w-3 h-3 rounded-full bg-red-400"></div>
              <span>Merah = Bahaya</span>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>

  <!-- SENSOR TRIGGER STATUS -->
  <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">

    <!-- PIR -->
    <div id="card-pir" class="flex items-center gap-4 p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all duration-500">
      <div id="icon-pir" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center transition-all duration-300">
        <i data-feather="eye" class="w-6 h-6 text-gray-400"></i>
      </div>
      <div>
        <p class="font-semibold text-gray-700 text-sm">Sensor PIR</p>
        <p id="status-pir" class="text-xs text-gray-400 mt-0.5">Tidak ada gerakan</p>
      </div>
      <div id="dot-pir" class="ml-auto w-3 h-3 rounded-full bg-gray-300"></div>
    </div>

    <!-- SW-420 -->
    <div id="card-sw420" class="flex items-center gap-4 p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all duration-500">
      <div id="icon-sw420" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center transition-all duration-300">
        <i data-feather="zap" class="w-6 h-6 text-gray-400"></i>
      </div>
      <div>
        <p class="font-semibold text-gray-700 text-sm">Sensor SW-420</p>
        <p id="status-sw420" class="text-xs text-gray-400 mt-0.5">Tidak ada getaran</p>
      </div>
      <div id="dot-sw420" class="ml-auto w-3 h-3 rounded-full bg-gray-300"></div>
    </div>

    <!-- Reed Switch -->
    <div id="card-reed" class="flex items-center gap-4 p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all duration-500">
      <div id="icon-reed" class="w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center transition-all duration-300">
        <i data-feather="unlock" class="w-6 h-6 text-gray-400"></i>
      </div>
      <div>
        <p class="font-semibold text-gray-700 text-sm">Reed Switch</p>
        <p id="status-reed" class="text-xs text-gray-400 mt-0.5">Pintu tertutup</p>
      </div>
      <div id="dot-reed" class="ml-auto w-3 h-3 rounded-full bg-gray-300"></div>
    </div>

  </div>

</div>

<!-- STATUS -->
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-100">

<div class="flex items-center gap-3 mb-8">
<div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
<i data-feather="activity" class="w-6 h-6 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Status Sistem</h3>
<p class="text-gray-600">Kondisi operasional saat ini</p>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<div class="group bg-gradient-to-br from-green-50 to-emerald-50 p-6 rounded-2xl border border-green-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="check" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-green-800">Semua sensor aktif</p>
<p class="text-green-600 text-sm">Monitoring berjalan normal</p>
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

<div class="group bg-gradient-to-br from-yellow-50 to-orange-50 p-6 rounded-2xl border border-yellow-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-yellow-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="alert-circle" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-yellow-800">Aktivitas terdeteksi hari ini</p>
<p class="text-yellow-600 text-sm">2 kejadian tercatat</p>
</div>
</div>
</div>

<div class="group bg-gradient-to-br from-purple-50 to-pink-50 p-6 rounded-2xl border border-purple-200 hover:shadow-lg transition-all duration-300">
<div class="flex items-center gap-4">
<div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
<i data-feather="clock" class="w-6 h-6 text-white"></i>
</div>
<div>
<p class="font-semibold text-purple-800">Update terakhir</p>
<p class="text-purple-600 text-sm">Update: {{ $lastUpdate ? $lastUpdate->diffForHumans() : 'Belum ada data' }}</p>
</div>
</div>
</div>

</div>

</div>

<!-- QUICK ACCESS -->
<div class="bg-white/80 backdrop-blur-sm rounded-3xl shadow-lg p-8 border border-gray-100">

<div class="flex items-center gap-3 mb-8">
<div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
<i data-feather="zap" class="w-6 h-6 text-white"></i>
</div>
<div>
<h3 class="text-xl font-display font-bold text-gray-800">Menu Cepat</h3>
<p class="text-gray-600">Akses fitur utama sistem</p>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

<a href="/monitoring" class="group bg-gradient-to-br from-blue-500 to-indigo-600 p-8 rounded-2xl text-white text-center font-semibold shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
<div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="activity" class="w-8 h-8"></i>
</div>
<h4 class="text-lg font-display font-bold mb-2">Monitoring Sensor</h4>
<p class="text-blue-100 text-sm">Pantau status sensor realtime</p>
</div>
</a>

<a href="/log" class="group bg-gradient-to-br from-gray-600 to-slate-700 p-8 rounded-2xl text-white text-center font-semibold shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
<div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="file-text" class="w-8 h-8"></i>
</div>
<h4 class="text-lg font-display font-bold mb-2">Log Aktivitas</h4>
<p class="text-gray-200 text-sm">Riwayat kejadian sistem</p>
</div>
</a>

<a href="/device" class="group bg-gradient-to-br from-green-500 to-emerald-600 p-8 rounded-2xl text-white text-center font-semibold shadow-lg hover:shadow-2xl hover:scale-105 transition-all duration-300 relative overflow-hidden">
<div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
<div class="relative z-10">
<div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
<i data-feather="cpu" class="w-8 h-8"></i>
</div>
<h4 class="text-lg font-display font-bold mb-2">Status Device</h4>
<p class="text-green-100 text-sm">Kondisi perangkat IoT</p>
</div>
</a>

</div>

</div>

</main>

</div>
</div>

<script>
feather.replace()
</script>

<script>
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

// Auto refresh halaman setiap 30 detik untuk update alert
setTimeout(function() {
    window.location.reload();
}, 30000);
</script>

<script>
// =============================================
// BUZZER & LED REALTIME POLLING
// =============================================

let alertDismissed = false;
let pollingInterval = null;

// Threshold waktu deteksi dianggap "aktif" (dalam detik)
const DETECTION_WINDOW = 30;

async function fetchSensorStatus() {
  try {
    const [pirRes, vibRes, reedRes] = await Promise.all([
      fetch('/api/pir/readings?limit=1').then(r => r.json()).catch(() => null),
      fetch('/api/vibration/readings?limit=1').then(r => r.json()).catch(() => null),
      fetch('/api/door-access/readings?limit=1').then(r => r.json()).catch(() => null),
    ]);

    const now = Date.now();

    // Cek PIR
    let pirActive = false;
    if (pirRes?.success && pirRes.data?.length > 0) {
      const latest = pirRes.data[0];
      const age = (now - new Date(latest.recorded_at).getTime()) / 1000;
      pirActive = latest.motion_detected && age <= DETECTION_WINDOW;
    }

    // Cek Vibration (SW-420)
    let sw420Active = false;
    if (vibRes?.success && vibRes.data?.length > 0) {
      const latest = vibRes.data[0];
      const age = (now - new Date(latest.recorded_at).getTime()) / 1000;
      sw420Active = latest.is_abnormal && age <= DETECTION_WINDOW;
    }

    // Cek Reed Switch
    let reedActive = false;
    if (reedRes?.success && reedRes.data?.length > 0) {
      const latest = reedRes.data[0];
      const age = (now - new Date(latest.recorded_at).getTime()) / 1000;
      reedActive = latest.door_open && age <= DETECTION_WINDOW;
    }

    const anyActive = pirActive || sw420Active || reedActive;

    updateUI(pirActive, sw420Active, reedActive, anyActive);
    updateLastUpdateText();

  } catch (err) {
    console.error('Polling error:', err);
  }
}

function updateUI(pirActive, sw420Active, reedActive, anyActive) {
  // --- SENSOR CARDS ---
  updateSensorCard('pir', pirActive, 'Gerakan terdeteksi!', 'Tidak ada gerakan');
  updateSensorCard('sw420', sw420Active, 'Getaran terdeteksi!', 'Tidak ada getaran');
  updateSensorCard('reed', reedActive, 'Pintu terbuka!', 'Pintu tertutup');

  // --- BUZZER ---
  const buzzerIcon = document.getElementById('buzzer-icon');
  const buzzerDot = document.getElementById('buzzer-status-dot');
  const buzzerText = document.getElementById('buzzer-status-text');
  const buzzerTrigger = document.getElementById('buzzer-trigger-text');
  const buzzerIconInner = document.getElementById('buzzer-icon-inner');

  if (anyActive) {
    buzzerIcon.className = 'relative w-16 h-16 bg-red-500 rounded-full flex items-center justify-center transition-all duration-300 buzzer-active';
    buzzerDot.className = 'w-4 h-4 rounded-full bg-red-500 animate-pulse transition-all duration-300';
    buzzerText.className = 'text-xl font-display font-bold text-red-600';
    buzzerText.textContent = 'BERBUNYI!';
    buzzerIconInner.setAttribute('data-feather', 'volume-2');

    const triggers = [];
    if (pirActive) triggers.push('PIR');
    if (sw420Active) triggers.push('SW-420');
    if (reedActive) triggers.push('Reed Switch');
    buzzerTrigger.textContent = 'Dipicu oleh: ' + triggers.join(', ');

    // Update trigger badges
    document.getElementById('buzzer-triggers').innerHTML = triggers.map(t =>
      `<span class="text-xs px-2 py-1 bg-red-100 text-red-600 rounded-lg font-semibold animate-pulse">${t}</span>`
    ).join('');
  } else {
    buzzerIcon.className = 'relative w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center transition-all duration-300';
    buzzerDot.className = 'w-4 h-4 rounded-full bg-gray-300 transition-all duration-300';
    buzzerText.className = 'text-xl font-display font-bold text-gray-400';
    buzzerText.textContent = 'Tidak Aktif';
    buzzerIconInner.setAttribute('data-feather', 'volume-x');
    buzzerTrigger.textContent = 'Menunggu deteksi sensor...';
    document.getElementById('buzzer-triggers').innerHTML = `
      <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded-lg">PIR</span>
      <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded-lg">SW-420</span>
      <span class="text-xs px-2 py-1 bg-gray-200 text-gray-500 rounded-lg">Reed Switch</span>
    `;
  }

  // --- LED ---
  const ledRed = document.getElementById('led-red');
  const ledGreen = document.getElementById('led-green');
  const ledStatusText = document.getElementById('led-status-text');
  const ledDetailText = document.getElementById('led-detail-text');

  if (anyActive) {
    // LED Merah menyala, hijau mati
    ledRed.className = 'w-8 h-8 rounded-full bg-red-100 transition-all duration-300 flex items-center justify-center';
    ledRed.style.boxShadow = '0 0 14px 5px #ef4444';
    ledRed.innerHTML = '<div class="w-4 h-4 rounded-full bg-red-500 animate-pulse"></div>';

    ledGreen.className = 'w-8 h-8 rounded-full bg-gray-100 transition-all duration-300 flex items-center justify-center';
    ledGreen.style.boxShadow = 'none';
    ledGreen.innerHTML = '<div class="w-4 h-4 rounded-full bg-gray-300"></div>';

    ledStatusText.className = 'text-xl font-display font-bold text-red-600';
    ledStatusText.textContent = 'BAHAYA!';
    ledDetailText.textContent = 'LED merah menyala — aktivitas mencurigakan terdeteksi';
  } else {
    // LED Hijau menyala, merah mati
    ledRed.className = 'w-8 h-8 rounded-full bg-gray-100 transition-all duration-300 flex items-center justify-center';
    ledRed.style.boxShadow = 'none';
    ledRed.innerHTML = '<div class="w-4 h-4 rounded-full bg-gray-300"></div>';

    ledGreen.className = 'w-8 h-8 rounded-full bg-green-100 transition-all duration-300 flex items-center justify-center';
    ledGreen.style.boxShadow = '0 0 10px 3px #22c55e';
    ledGreen.innerHTML = '<div class="w-4 h-4 rounded-full bg-green-500"></div>';

    ledStatusText.className = 'text-xl font-display font-bold text-green-600';
    ledStatusText.textContent = 'Aman';
    ledDetailText.textContent = 'LED hijau menyala — sistem normal';
  }

  // --- ALERT BANNER ---
  const banner = document.getElementById('alert-banner');
  if (anyActive && !alertDismissed) {
    const msgs = [];
    if (pirActive) msgs.push('gerakan (PIR)');
    if (sw420Active) msgs.push('getaran (SW-420)');
    if (reedActive) msgs.push('pintu terbuka (Reed Switch)');
    document.getElementById('alert-banner-text').textContent =
      'Terdeteksi: ' + msgs.join(', ') + ' — Buzzer & LED merah aktif!';
    banner.classList.remove('hidden');
  } else if (!anyActive) {
    banner.classList.add('hidden');
    alertDismissed = false;
  }

  // Re-render feather icons
  feather.replace();
}

function updateSensorCard(id, active, activeText, inactiveText) {
  const card = document.getElementById('card-' + id);
  const icon = document.getElementById('icon-' + id);
  const status = document.getElementById('status-' + id);
  const dot = document.getElementById('dot-' + id);

  if (active) {
    card.className = 'flex items-center gap-4 p-4 rounded-2xl border-2 border-red-300 bg-red-50 transition-all duration-500';
    icon.className = 'w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center transition-all duration-300';
    icon.querySelector('i').className = icon.querySelector('i').className.replace('text-gray-400', 'text-white');
    status.textContent = activeText;
    status.className = 'text-xs text-red-600 font-semibold mt-0.5';
    dot.className = 'ml-auto w-3 h-3 rounded-full bg-red-500 animate-pulse';
  } else {
    card.className = 'flex items-center gap-4 p-4 rounded-2xl border-2 border-gray-100 bg-white transition-all duration-500';
    icon.className = 'w-12 h-12 bg-gray-100 rounded-xl flex items-center justify-center transition-all duration-300';
    status.textContent = inactiveText;
    status.className = 'text-xs text-gray-400 mt-0.5';
    dot.className = 'ml-auto w-3 h-3 rounded-full bg-gray-300';
  }
}

function updateLastUpdateText() {
  const now = new Date();
  const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
  document.getElementById('last-update-text').textContent = 'Update: ' + timeStr;
}

function dismissAlert() {
  alertDismissed = true;
  document.getElementById('alert-banner').classList.add('hidden');
}

// Mulai polling setiap 5 detik
document.addEventListener('DOMContentLoaded', function () {
  fetchSensorStatus();
  pollingInterval = setInterval(fetchSensorStatus, 5000);
});
</script>

</body>
</html>