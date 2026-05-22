<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Smart Rack Security</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .glass { backdrop-filter: blur(16px); background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); }
  .animate-float { animation: float 6s ease-in-out infinite; }
  @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-20px); } }
</style>
</head>
<body class="bg-gradient-to-br from-indigo-600 via-purple-700 to-blue-800 flex items-center justify-center min-h-screen overflow-hidden relative">

<!-- Background Pattern -->
<div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>

<!-- Floating Elements -->
<div class="absolute top-20 left-10 w-4 h-4 bg-blue-400 rounded-full animate-pulse opacity-60"></div>
<div class="absolute top-40 right-20 w-6 h-6 bg-purple-400 rounded-full animate-bounce opacity-40"></div>
<div class="absolute bottom-20 left-20 w-3 h-3 bg-indigo-300 rounded-full animate-ping opacity-50"></div>
<div class="absolute bottom-40 right-10 w-5 h-5 bg-pink-400 rounded-full animate-pulse opacity-30"></div>

<!-- Login Card -->
<div class="relative z-10 w-full max-w-md mx-auto px-6">

<div class="glass rounded-3xl p-10 shadow-2xl border border-white/20 backdrop-blur-xl">

<!-- Logo & Title -->
<div class="text-center mb-10">
<div class="animate-float mb-6">
<div class="w-20 h-20 bg-white/10 backdrop-blur-sm rounded-2xl flex items-center justify-center mx-auto border border-white/20">
<i data-feather="shield" class="w-10 h-10 text-white"></i>
</div>
</div>

<h1 class="text-3xl font-display font-bold text-white mb-2">Smart Rack Security</h1>
<p class="text-blue-100">Sistem Monitoring Keamanan IoT</p>
</div>

<!-- Form -->
<form action="{{ route('login.process') }}" method="POST" class="space-y-6">
@csrf

<!-- Alert Messages -->
@if(session('error'))
<div class="bg-red-500/10 border border-red-500/20 text-red-200 px-4 py-3 rounded-2xl text-sm">
{{ session('error') }}
</div>
@endif

@if(session('success'))
<div class="bg-green-500/10 border border-green-500/20 text-green-200 px-4 py-3 rounded-2xl text-sm">
{{ session('success') }}
</div>
@endif

<!-- Demo Info -->
<div class="bg-blue-500/10 border border-blue-500/20 text-blue-200 px-4 py-3 rounded-2xl text-sm text-center">
<p>Masukkan email dan password akun Anda untuk masuk</p>
</div>

<!-- Username -->
<div class="space-y-2">
<label class="block text-white font-medium">Username</label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
<i data-feather="user" class="w-5 h-5 text-blue-200"></i>
</div>
<input type="text" name="username" placeholder="Masukkan email" value="{{ old('username') }}"
class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all duration-300 backdrop-blur-sm" required>
</div>
</div>

<!-- Password -->
<div class="space-y-2">
<label class="block text-white font-medium">Password</label>
<div class="relative">
<div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
<i data-feather="lock" class="w-5 h-5 text-blue-200"></i>
</div>
<input type="password" name="password" placeholder="Masukkan password"
class="w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-blue-200 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all duration-300 backdrop-blur-sm" required>
</div>
</div>

<!-- Remember Me -->
<div class="flex items-center">
<label class="flex items-center gap-3 cursor-pointer">
<input type="checkbox" name="remember" class="w-4 h-4 text-indigo-600 bg-white/10 border-white/20 rounded focus:ring-white/30">
<span class="text-blue-100 text-sm">Ingat saya</span>
</label>
</div>

<!-- Login Button -->
<button type="submit" class="group w-full bg-white text-indigo-600 py-4 rounded-2xl font-semibold hover:shadow-2xl hover:scale-105 transition-all duration-300 flex items-center justify-center gap-3">
<span>Masuk Dashboard</span>
<i data-feather="arrow-right" class="w-5 h-5 group-hover:translate-x-1 transition-transform"></i>
</button>

</form>

<script>
feather.replace();
</script>

<!-- Footer -->
<div class="text-center mt-8 pt-6 border-t border-white/10 space-y-3">
<p class="text-blue-100 text-sm">Sistem Monitoring Rak Barang</p>
<p class="text-blue-100 text-sm">Belum punya akun?
<a href="/register" class="text-white font-semibold hover:underline">Daftar di sini</a>
</p>
<a href="/home" class="text-blue-200 hover:text-white text-sm transition-colors flex items-center justify-center gap-2">
<i data-feather="arrow-left" class="w-4 h-4"></i>
<span>Kembali ke Home</span>
</a>
</div>

</div>

</div>

</body>
</html>