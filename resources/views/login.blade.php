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
  .glass {
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
  }
  .animate-float { animation: float 6s ease-in-out infinite; }
  @keyframes float {
    0%, 100% { transform: translateY(0px); }
    50%       { transform: translateY(-12px); }
  }
  /* Autofill fix — jaga warna teks tetap putih */
  input:-webkit-autofill,
  input:-webkit-autofill:hover,
  input:-webkit-autofill:focus {
    -webkit-text-fill-color: #fff;
    -webkit-box-shadow: 0 0 0px 1000px rgba(255,255,255,0.08) inset;
    transition: background-color 5000s ease-in-out 0s;
  }
</style>
</head>

<body class="bg-gradient-to-br from-indigo-600 via-purple-700 to-blue-800 min-h-screen flex items-center justify-center py-8 px-4 relative">

<!-- Background pattern -->
<div class="fixed inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20 pointer-events-none"></div>

<!-- Floating decorations — hidden on very small screens -->
<div class="hidden sm:block fixed top-16 left-8 w-4 h-4 bg-blue-400 rounded-full animate-pulse opacity-60 pointer-events-none"></div>
<div class="hidden sm:block fixed top-36 right-12 w-6 h-6 bg-purple-400 rounded-full animate-bounce opacity-40 pointer-events-none"></div>
<div class="hidden sm:block fixed bottom-24 left-16 w-3 h-3 bg-indigo-300 rounded-full animate-ping opacity-50 pointer-events-none"></div>
<div class="hidden sm:block fixed bottom-40 right-8 w-5 h-5 bg-pink-400 rounded-full animate-pulse opacity-30 pointer-events-none"></div>

<!-- Card -->
<div class="relative z-10 w-full max-w-sm sm:max-w-md">

  <div class="glass rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-white/20">

    <!-- Logo & Title -->
    <div class="text-center mb-7 sm:mb-9">
      <div class="animate-float mb-4 sm:mb-6">
        <div class="w-14 h-14 sm:w-18 sm:h-18 md:w-20 md:h-20 bg-white/10 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto border border-white/20">
          <i data-feather="shield" class="w-7 h-7 sm:w-9 sm:h-9 md:w-10 md:h-10 text-white"></i>
        </div>
      </div>
      <h1 class="text-2xl sm:text-3xl font-display font-bold text-white mb-1 sm:mb-2">Smart Rack Security</h1>
      <p class="text-blue-100 text-sm sm:text-base">Sistem Monitoring Keamanan IoT</p>
    </div>

    <!-- Form -->
    <form action="{{ route('login.process') }}" method="POST" class="space-y-4 sm:space-y-5">
      @csrf

      {{-- Error / Success alerts --}}
      @if(session('error'))
      <div class="bg-red-500/15 border border-red-400/30 text-red-200 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
        <i data-feather="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
        <span>{{ session('error') }}</span>
      </div>
      @endif

      @if(session('success'))
      <div class="bg-green-500/15 border border-green-400/30 text-green-200 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
        <i data-feather="check-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
        <span>{{ session('success') }}</span>
      </div>
      @endif

      <!-- Info -->
      <div class="bg-blue-500/10 border border-blue-400/20 text-blue-200 px-4 py-3 rounded-xl text-xs sm:text-sm text-center">
        Masukkan email dan password akun Anda untuk masuk
      </div>

      <!-- Email / Username -->
      <div class="space-y-1.5">
        <label class="block text-white text-sm font-medium">Email</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i data-feather="user" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-200"></i>
          </div>
          <input
            type="text"
            name="username"
            placeholder="Masukkan email"
            value="{{ old('username') }}"
            autocomplete="username"
            class="w-full pl-10 sm:pl-12 pr-4 py-3 sm:py-3.5 bg-white/10 border border-white/20 rounded-xl text-white text-sm sm:text-base placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all duration-300"
            required
          >
        </div>
        @error('username')
        <p class="text-red-300 text-xs flex items-center gap-1 mt-1">
          <i data-feather="alert-circle" class="w-3 h-3"></i> {{ $message }}
        </p>
        @enderror
      </div>

      <!-- Password -->
      <div class="space-y-1.5">
        <label class="block text-white text-sm font-medium">Password</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i data-feather="lock" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-200"></i>
          </div>
          <input
            type="password"
            name="password"
            id="password-input"
            placeholder="Masukkan password"
            autocomplete="current-password"
            class="w-full pl-10 sm:pl-12 pr-11 py-3 sm:py-3.5 bg-white/10 border border-white/20 rounded-xl text-white text-sm sm:text-base placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all duration-300"
            required
          >
          <!-- Toggle show/hide password -->
          <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-blue-200 hover:text-white transition-colors">
            <i id="eye-icon" data-feather="eye" class="w-4 h-4 sm:w-5 sm:h-5"></i>
          </button>
        </div>
        @error('password')
        <p class="text-red-300 text-xs flex items-center gap-1 mt-1">
          <i data-feather="alert-circle" class="w-3 h-3"></i> {{ $message }}
        </p>
        @enderror
      </div>

      <!-- Remember Me -->
      <div class="flex items-center">
        <label class="flex items-center gap-2.5 cursor-pointer select-none">
          <input type="checkbox" name="remember" class="w-4 h-4 rounded border-white/30 bg-white/10 text-indigo-500 focus:ring-white/30 focus:ring-offset-0">
          <span class="text-blue-100 text-sm">Ingat saya</span>
        </label>
      </div>

      <!-- Submit -->
      <button
        type="submit"
        class="group w-full bg-white text-indigo-600 py-3 sm:py-3.5 rounded-xl font-semibold text-sm sm:text-base hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 mt-2"
      >
        <span>Masuk Dashboard</span>
        <i data-feather="arrow-right" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform"></i>
      </button>

    </form>

    <!-- Footer links -->
    <div class="text-center mt-6 pt-5 border-t border-white/10 space-y-2.5">
      <p class="text-blue-100 text-xs sm:text-sm">
        Belum punya akun?
        <a href="/register" class="text-white font-semibold hover:underline ml-1">Daftar di sini</a>
      </p>
      <a href="/home" class="text-blue-200 hover:text-white text-xs sm:text-sm transition-colors inline-flex items-center justify-center gap-1.5">
        <i data-feather="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Kembali ke Home</span>
      </a>
    </div>

  </div>
</div>

<script>
feather.replace();

function togglePassword() {
  const input = document.getElementById('password-input');
  const icon  = document.getElementById('eye-icon');
  if (input.type === 'password') {
    input.type = 'text';
    icon.setAttribute('data-feather', 'eye-off');
  } else {
    input.type = 'password';
    icon.setAttribute('data-feather', 'eye');
  }
  feather.replace();
}
</script>

</body>
</html>
