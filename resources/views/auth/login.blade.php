@extends('layouts.guest')

@section('content')
<div class="relative z-1 bg-white p-6 sm:p-0 dark:bg-slate-900">
  <div class="relative flex h-screen w-full flex-col justify-center sm:p-0 lg:flex-row dark:bg-slate-900">
    
    <!-- Form Section -->
    <div class="flex w-full flex-1 flex-col lg:w-1/2">
      
      <!-- Theme Toggle (Optional for Login) -->
      <div class="absolute top-6 right-6 z-50">
        <button @click="darkMode = !darkMode" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-100 transition-colors">
            <i class="fa-solid fa-sun text-xl" x-show="darkMode" style="display: none;"></i>
            <i class="fa-solid fa-moon text-xl" x-show="!darkMode"></i>
        </button>
      </div>

      <div class="mx-auto flex w-full max-w-md flex-1 flex-col justify-center">
        <div>
          <div class="mb-5 sm:mb-8 text-center lg:text-left">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-600 text-white mb-6">
                <i class="fa-solid fa-graduation-cap text-3xl"></i>
            </div>
            <h1 class="text-2xl sm:text-3xl mb-2 font-bold text-slate-800 dark:text-white/90">
              SIM Sekolah
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">
              Sistem Informasi Akademik & Kesiswaan
            </p>
          </div>

          @if(session('error'))
            <div class="mb-6 p-4 rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400 border border-rose-200 dark:border-rose-500/20 text-sm text-center">
                {{ session('error') }}
            </div>
          @endif

          <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="space-y-5">
              <!-- NIP / Username -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-400">
                  NIP / Username <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400">
                    <i class="fa-solid fa-user"></i>
                  </span>
                  <input
                    type="text"
                    name="nip"
                    placeholder="Masukkan NIP / Username"
                    required
                    class="h-11 w-full rounded-lg border border-slate-300 bg-transparent py-2.5 pl-11 pr-4 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-white/90 dark:focus:border-blue-500 transition-colors"
                  />
                </div>
              </div>

              <!-- Password -->
              <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-400">
                  Password <span class="text-rose-500">*</span>
                </label>
                <div x-data="{ showPassword: false }" class="relative">
                  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400">
                    <i class="fa-solid fa-lock"></i>
                  </span>
                  <input
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    placeholder="Masukkan Password"
                    required
                    class="h-11 w-full rounded-lg border border-slate-300 bg-transparent py-2.5 pl-11 pr-11 text-sm text-slate-800 placeholder:text-slate-400 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-white/90 dark:focus:border-blue-500 transition-colors"
                  />
                  <span
                    @click="showPassword = !showPassword"
                    class="absolute right-4 top-1/2 -translate-y-1/2 cursor-pointer text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors"
                  >
                    <i class="fa-regular fa-eye-slash" x-show="!showPassword"></i>
                    <i class="fa-regular fa-eye" x-show="showPassword" style="display: none;"></i>
                  </span>
                </div>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                class="flex w-full items-center justify-center rounded-lg bg-blue-600 p-3 text-sm font-medium text-white transition-colors hover:bg-blue-500 mt-2"
              >
                Sign In
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Image Section (Hidden on mobile) -->
    <div class="hidden w-full lg:block lg:w-1/2 bg-slate-50 dark:bg-slate-800/50 relative overflow-hidden">
      <div class="absolute inset-0 flex flex-col items-center justify-center p-12 text-center z-10">
        <h2 class="text-3xl font-bold text-slate-800 dark:text-white/90 mb-4">Selamat Datang di SIM Sekolah</h2>
        <p class="text-slate-500 dark:text-slate-400 max-w-md">
          Sistem Informasi Manajemen terpadu untuk memantau akademik, presensi, dan kedisiplinan siswa secara real-time.
        </p>
        
        <!-- Decorative Grid/Image from template -->
        <div class="mt-12 relative w-full max-w-lg">
            <img src="{{ asset('assets/img/grid-image/image-01.png') }}" alt="Dashboard Preview" class="w-full h-auto rounded-xl shadow-2xl border border-slate-200 dark:border-slate-700/60 opacity-90 dark:opacity-70 transform rotate-2 hover:rotate-0 transition-transform duration-500" onerror="this.style.display='none'">
        </div>
      </div>
      
      <!-- Decorative Background Elements -->
      <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-blue-500/5 dark:bg-blue-500/10 blur-3xl"></div>
        <div class="absolute -bottom-[20%] -left-[10%] w-[60%] h-[60%] rounded-full bg-emerald-500/5 dark:bg-emerald-500/10 blur-3xl"></div>
      </div>
    </div>

  </div>
</div>
@endsection
