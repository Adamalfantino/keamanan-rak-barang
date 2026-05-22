<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/home', function () {
    return view('home');
})->name('home.page');

Route::get('/login', function () {
    return view('login');
})->name('login');

// Halaman Register
Route::get('/register', function () {
    return view('register');
})->name('register');

// Proses Register
Route::post('/register', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|string|min:8|confirmed',
    ], [
        'name.required'      => 'Nama lengkap wajib diisi.',
        'email.required'     => 'Email wajib diisi.',
        'email.email'        => 'Format email tidak valid.',
        'email.unique'       => 'Email sudah terdaftar, gunakan email lain.',
        'password.required'  => 'Password wajib diisi.',
        'password.min'       => 'Password minimal 8 karakter.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    $user = \App\Models\User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => $request->password,
    ]);

    \Illuminate\Support\Facades\Auth::login($user);

    return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat! Selamat datang, ' . $user->name . '.');
})->name('register.process');

// Route untuk proses login
Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    // Cek kredensial ke tabel users (kolom email = username)
    $credentials = [
        'email'    => $request->input('username'),
        'password' => $request->input('password'),
    ];

    if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->route('dashboard')->with('success', 'Login berhasil!');
    }

    return back()->with('error', 'Email atau password salah. Silakan coba lagi.')->withInput(['username' => $request->input('username')]);
})->name('login.process');

// Route untuk logout
Route::get('/logout', function () {
    \Illuminate\Support\Facades\Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('success', 'Logout berhasil!');
})->name('logout');

// Group routes yang memerlukan login
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $totalSensor       = \App\Models\Sensor::where('is_active', true)->count();
        $sensorAktif       = \App\Models\Sensor::where('status', 'active')->count();
        $peringatanHariIni = \App\Models\Alert::whereDate('triggered_at', today())->count();
        $alertAktif        = \App\Models\Alert::where('status', 'active')->count();
        $lastUpdate        = \App\Models\ActivityLog::latest('event_time')->first()?->event_time;

        // Alert terbaru untuk ditampilkan di dashboard
        $alertTerbaru = \App\Models\Alert::with('device')
            ->where('status', 'active')
            ->orderBy('triggered_at', 'desc')
            ->limit(5)
            ->get();

        // Deteksi sensor aktif dalam 30 detik terakhir
        $pirAktif  = \App\Models\PirReading::where('recorded_at', '>=', now()->subSeconds(30))
                        ->where('motion_detected', true)->exists();
        $vibAktif  = \App\Models\VibrationReading::where('recorded_at', '>=', now()->subSeconds(30))
                        ->where('is_abnormal', true)->exists();
        $reedAktif = \App\Models\ReedSwitchReading::where('recorded_at', '>=', now()->subSeconds(30))
                        ->where('door_open', true)->exists();

        return view('dashboard', compact(
            'totalSensor', 'sensorAktif', 'peringatanHariIni', 'alertAktif',
            'lastUpdate', 'alertTerbaru', 'pirAktif', 'vibAktif', 'reedAktif'
        ));
    })->name('dashboard');

    Route::get('/monitoring', function () {
        // Ambil data terbaru dari tabel spesifik sensor
        $pirLatest       = \App\Models\PirReading::with('device')
                            ->orderBy('recorded_at', 'desc')->first();
        $vibrationLatest = \App\Models\VibrationReading::with('device')
                            ->orderBy('recorded_at', 'desc')->first();
        $reedLatest      = \App\Models\ReedSwitchReading::with('device')
                            ->orderBy('recorded_at', 'desc')->first();

        // Ambil data sensor dari tabel sensors untuk info nama & status
        $pirSensor       = \App\Models\Sensor::where('type', 'pir')->first();
        $vibrationSensor = \App\Models\Sensor::where('type', 'vibration')->first();
        $reedSensor      = \App\Models\Sensor::where('type', 'reed_switch')->first();

        // Statistik 24 jam terakhir
        $pirCount24h        = \App\Models\PirReading::where('recorded_at', '>=', now()->subHours(24))
                                ->where('motion_detected', true)->count();
        $vibrationCount24h  = \App\Models\VibrationReading::where('recorded_at', '>=', now()->subHours(24))
                                ->where('is_abnormal', true)->count();
        $reedCount24h       = \App\Models\ReedSwitchReading::where('recorded_at', '>=', now()->subHours(24))
                                ->where('door_open', true)->count();

        // Riwayat 5 data terakhir tiap sensor
        $pirHistory         = \App\Models\PirReading::orderBy('recorded_at', 'desc')->limit(5)->get();
        $vibrationHistory   = \App\Models\VibrationReading::orderBy('recorded_at', 'desc')->limit(5)->get();
        $reedHistory        = \App\Models\ReedSwitchReading::orderBy('recorded_at', 'desc')->limit(5)->get();

        $device             = \App\Models\Device::where('status', 'online')->first();
        $lastUpdate         = \App\Models\PirReading::orderBy('recorded_at', 'desc')->first()?->recorded_at;

        return view('monitoring', compact(
            'pirSensor', 'vibrationSensor', 'reedSensor',
            'pirLatest', 'vibrationLatest', 'reedLatest',
            'pirCount24h', 'vibrationCount24h', 'reedCount24h',
            'pirHistory', 'vibrationHistory', 'reedHistory',
            'device', 'lastUpdate'
        ));
    })->name('monitoring');

    Route::get('/log', function () {
        $logs   = \App\Models\ActivityLog::with(['device', 'sensor'])
                    ->orderBy('event_time', 'desc')
                    ->paginate(20);
        $totalLog       = \App\Models\ActivityLog::count();
        $logHariIni     = \App\Models\ActivityLog::whereDate('event_time', today())->count();
        $logWarning     = \App\Models\ActivityLog::where('severity', 'warning')->count();
        $logCritical    = \App\Models\ActivityLog::where('severity', 'critical')->count();

        return view('log', compact('logs', 'totalLog', 'logHariIni', 'logWarning', 'logCritical'));
    })->name('log');

    Route::get('/device', function () {
        return view('device');
    })->name('device');
});