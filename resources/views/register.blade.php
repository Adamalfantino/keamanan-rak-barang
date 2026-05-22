<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Smart Rack Security</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/feather-icons"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; }
  .font-display { font-family: 'Space Grotesk', sans-serif; }
  .glass { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); }
  .animate-float { animation: float 6s ease-in-out infinite; }
  @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-12px)} }
  input:-webkit-autofill,input:-webkit-autofill:hover,input:-webkit-autofill:focus {
    -webkit-text-fill-color:#fff;
    -webkit-box-shadow:0 0 0 1000px rgba(255,255,255,0.08) inset;
    transition:background-color 5000s ease-in-out 0s;
  }
</style>
</head>

<body class="bg-gradient-to-br from-indigo-600 via-purple-700 to-blue-800 min-h-screen flex items-center justify-center py-8 px-4 relative">

<!-- Background pattern -->
<div class="fixed inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.05"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20 pointer-events-none"></div>

<!-- Floating decorations -->
<div class="hidden sm:block fixed top-16 left-8 w-4 h-4 bg-blue-400 rounded-full animate-pulse opacity-60 pointer-events-none"></div>
<div class="hidden sm:block fixed top-36 right-12 w-6 h-6 bg-purple-400 rounded-full animate-bounce opacity-40 pointer-events-none"></div>
<div class="hidden sm:block fixed bottom-24 left-16 w-3 h-3 bg-indigo-300 rounded-full animate-ping opacity-50 pointer-events-none"></div>
<div class="hidden sm:block fixed bottom-40 right-8 w-5 h-5 bg-pink-400 rounded-full animate-pulse opacity-30 pointer-events-none"></div>

<!-- Card -->
<div class="relative z-10 w-full max-w-sm sm:max-w-md">
  <div class="glass rounded-2xl sm:rounded-3xl p-6 sm:p-8 md:p-10 shadow-2xl border border-white/20">

    <!-- Logo & Title -->
    <div class="text-center mb-6 sm:mb-8">
      <div class="animate-float mb-4 sm:mb-5">
        <div class="w-14 h-14 sm:w-16 sm:h-16 md:w-20 md:h-20 bg-white/10 backdrop-blur-sm rounded-xl sm:rounded-2xl flex items-center justify-center mx-auto border border-white/20">
          <i data-feather="user-plus" class="w-7 h-7 sm:w-8 sm:h-8 md:w-10 md:h-10 text-white"></i>
        </div>
      </div>
      <h1 class="text-2xl sm:text-3xl font-display font-bold text-white mb-1 sm:mb-2">Buat Akun</h1>
      <p class="text-blue-100 text-sm sm:text-base">Daftar untuk akses Smart Rack Security</p>
    </div>

    <!-- Form -->
    <form action="{{ route('register.process') }}" method="POST" class="space-y-4 sm:space-y-5">
      @csrf

      @if(session('error'))
      <div class="bg-red-500/15 border border-red-400/30 text-red-200 px-4 py-3 rounded-xl text-sm flex items-start gap-2">
        <i data-feather="alert-circle" class="w-4 h-4 mt-0.5 flex-shrink-0"></i>
        <span>{{ session('error') }}</span>
      </div>
      @endif

      @if($errors->any())
      <div class="bg-red-500/15 border border-red-400/30 text-red-200 px-4 py-3 rounded-xl text-sm space-y-1">
        @foreach($errors->all() as $error)
        <p class="flex items-start gap-1.5"><i data-feather="alert-circle" class="w-3.5 h-3.5 mt-0.5 flex-shrink-0"></i>{{ $error }}</p>
        @endforeach
      </div>
      @endif

      <!-- Nama -->
      <div class="space-y-1.5">
        <label class="block text-white text-sm font-medium">Nama Lengkap</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i data-feather="user" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-200"></i>
          </div>
          <input type="text" name="name" placeholder="Masukkan nama lengkap" value="{{ old('name') }}"
            autocomplete="name"
            class="w-full pl-10 sm:pl-12 pr-4 py-3 sm:py-3.5 bg-white/10 border border-white/20 rounded-xl text-white text-sm sm:text-base placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all" required>
        </div>
        @error('name')<p class="text-red-300 text-xs mt-1 flex items-center gap-1"><i data-feather="alert-circle" class="w-3 h-3"></i>{{ $message }}</p>@enderror
      </div>

      <!-- Email -->
      <div class="space-y-1.5">
        <label class="block text-white text-sm font-medium">Email</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i data-feather="mail" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-200"></i>
          </div>
          <input type="email" name="email" placeholder="Masukkan email" value="{{ old('email') }}"
            autocomplete="email"
            class="w-full pl-10 sm:pl-12 pr-4 py-3 sm:py-3.5 bg-white/10 border border-white/20 rounded-xl text-white text-sm sm:text-base placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all" required>
        </div>
        @error('email')<p class="text-red-300 text-xs mt-1 flex items-center gap-1"><i data-feather="alert-circle" class="w-3 h-3"></i>{{ $message }}</p>@enderror
      </div>

      <!-- Password -->
      <div class="space-y-1.5">
        <label class="block text-white text-sm font-medium">Password</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i data-feather="lock" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-200"></i>
          </div>
          <input type="password" name="password" id="pw1" placeholder="Minimal 8 karakter"
            autocomplete="new-password"
            class="w-full pl-10 sm:pl-12 pr-11 py-3 sm:py-3.5 bg-white/10 border border-white/20 rounded-xl text-white text-sm sm:text-base placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all" required>
          <button type="button" onclick="togglePw('pw1','eye1')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-blue-200 hover:text-white transition-colors">
            <i id="eye1" data-feather="eye" class="w-4 h-4 sm:w-5 sm:h-5"></i>
          </button>
        </div>
        @error('password')<p class="text-red-300 text-xs mt-1 flex items-center gap-1"><i data-feather="alert-circle" class="w-3 h-3"></i>{{ $message }}</p>@enderror
      </div>

      <!-- Konfirmasi Password -->
      <div class="space-y-1.5">
        <label class="block text-white text-sm font-medium">Konfirmasi Password</label>
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
            <i data-feather="lock" class="w-4 h-4 sm:w-5 sm:h-5 text-blue-200"></i>
          </div>
          <input type="password" name="password_confirmation" id="pw2" placeholder="Ulangi password"
            autocomplete="new-password"
            class="w-full pl-10 sm:pl-12 pr-11 py-3 sm:py-3.5 bg-white/10 border border-white/20 rounded-xl text-white text-sm sm:text-base placeholder-blue-300 focus:outline-none focus:ring-2 focus:ring-white/30 focus:border-white/40 transition-all" required>
          <button type="button" onclick="togglePw('pw2','eye2')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-blue-200 hover:text-white transition-colors">
            <i id="eye2" data-feather="eye" class="w-4 h-4 sm:w-5 sm:h-5"></i>
          </button>
        </div>
      </div>

      <!-- Submit -->
      <button type="submit"
        class="group w-full bg-white text-indigo-600 py-3 sm:py-3.5 rounded-xl font-semibold text-sm sm:text-base hover:shadow-2xl hover:scale-[1.02] active:scale-[0.98] transition-all duration-300 flex items-center justify-center gap-2 mt-2">
        <span>Daftar Sekarang</span>
        <i data-feather="arrow-right" class="w-4 h-4 sm:w-5 sm:h-5 group-hover:translate-x-1 transition-transform"></i>
      </button>
    </form>

    <!-- Footer -->
    <div class="text-center mt-5 sm:mt-6 pt-5 border-t border-white/10 space-y-2.5">
      <p class="text-blue-100 text-xs sm:text-sm">Sudah punya akun?</p>
      <a href="/login" class="inline-flex items-center gap-2 text-white font-semibold bg-white/10 hover:bg-white/20 px-5 py-2.5 rounded-xl transition-all text-sm">
        <i data-feather="log-in" class="w-4 h-4"></i>
        <span>Masuk Sekarang</span>
      </a>
      <div>
        <a href="/home" class="text-blue-200 hover:text-white text-xs sm:text-sm transition-colors inline-flex items-center gap-1.5">
          <i data-feather="arrow-left" class="w-3.5 h-3.5"></i> Kembali ke Home
        </a>
      </div>
    </div>

  </div>
</div>

<script>
feather.replace();
function togglePw(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon  = document.getElementById(iconId);
  input.type  = input.type === 'password' ? 'text' : 'password';
  icon.setAttribute('data-feather', input.type === 'password' ? 'eye' : 'eye-off');
  feather.replace();
}
</script>
</body>
</html>
