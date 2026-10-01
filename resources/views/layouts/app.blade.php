<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Blogwalker Pro') }}</title>
    
    <!-- Favicon & Mobile Touch Icons -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">
    <meta name="theme-color" content="#059669">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js CDN -->
    <script defer src="https://unpkg.com/alpinejs@3.14.3/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans" x-data="{ sidebarOpen: false }">

    @auth
    <!-- AUTHENTICATED USER: SIDEBAR LAYOUT -->
    <div class="min-h-screen flex">

        <!-- Mobile Sidebar Backdrop Overlay -->
        <div x-show="sidebarOpen" 
             @click="sidebarOpen = false" 
             x-cloak 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-40 lg:hidden">
        </div>

        <!-- Sidebar Navigation Container -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:sticky top-0 inset-y-0 left-0 z-50 w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between transition-transform duration-200 ease-in-out h-screen shadow-lg lg:shadow-none">
            
            <!-- Sidebar Header & Menu -->
            <div class="flex-1 flex flex-col min-h-0 overflow-y-auto">
                
                <!-- Logo & Brand Header -->
                <div class="h-16 px-5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
                    <a href="{{ auth()->user()->isAdmin() ? route('admin.dashboard') : route('blogwalker.dashboard') }}" class="flex items-center gap-2.5 group">
                        <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm shadow-emerald-200 group-hover:bg-emerald-700 transition">
                            BW
                        </div>
                        <div class="flex flex-col">
                            <span class="font-black text-slate-900 leading-tight tracking-tight text-base">Blogwalker<span class="text-emerald-600">Pro</span></span>
                            <span class="text-[10px] text-slate-400 font-semibold tracking-wider uppercase">Link Building Team</span>
                        </div>
                    </a>

                    <!-- Close button for mobile -->
                    <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Links List -->
                <nav class="p-3.5 space-y-6 flex-1">
                    
                    @if(auth()->user()->isAdmin())
                        @php
                            try {
                                $pendingCount = \Illuminate\Support\Facades\Schema::hasTable('submissions') 
                                    ? \App\Models\Submission::where('review_status', 'pending')->count() 
                                    : 0;
                                $pendingRegCount = \Illuminate\Support\Facades\Schema::hasTable('users') 
                                    ? \App\Models\User::where('role', 'blogwalker')->where('approval_status', 'pending')->count() 
                                    : 0;
                                $pendingProfileChangeCount = \Illuminate\Support\Facades\Schema::hasTable('profile_change_requests') 
                                    ? \App\Models\ProfileChangeRequest::where('status', 'pending')->count() 
                                    : 0;
                                $availableTargetsCount = \Illuminate\Support\Facades\Schema::hasTable('target_urls') 
                                    ? \App\Models\TargetUrl::available()->count() 
                                    : 0;
                            } catch (\Throwable $e) {
                                $pendingCount = 0;
                                $pendingRegCount = 0;
                                $pendingProfileChangeCount = 0;
                                $availableTargetsCount = 0;
                            }
                        @endphp

                        <!-- GRUP: UTAMA -->
                        <div class="space-y-1">
                            <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Utama</div>

                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.dashboard') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard Utama</span>
                            </a>

                            <a href="{{ route('admin.reviews.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.reviews.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('admin.reviews.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                    </svg>
                                    <span>Verifikasi Tugas & Misi</span>
                                </div>
                                @if($pendingCount > 0)
                                    <span class="px-2 py-0.5 text-[11px] font-bold bg-amber-500 text-white rounded-full">{{ $pendingCount }}</span>
                                @endif
                            </a>
                        </div>

                        <!-- GRUP: OPERASIONAL & TARGET -->
                        <div class="space-y-1">
                            <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Operasional & Target</div>

                            <a href="{{ route('admin.targets.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.targets.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('admin.targets.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                    <span>Antrean Target URL</span>
                                </div>
                                @if($availableTargetsCount > 0)
                                    <span class="px-2 py-0.5 text-[11px] font-bold bg-emerald-600 text-white rounded-full">{{ $availableTargetsCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('admin.domains.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.domains.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.domains.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <span>Monitoring Domain & Kuota</span>
                            </a>

                            <a href="{{ route('admin.periods.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.periods.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.periods.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Periode & Target Bulanan</span>
                            </a>
                        </div>

                        <!-- GRUP: TIM & PAYROLL -->
                        <div class="space-y-1">
                            <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Tim & Payroll</div>

                            <a href="{{ route('admin.workers.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.workers.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.workers.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Daftar Blogwalker & Plotting</span>
                            </a>

                            <a href="{{ route('admin.registrations.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.registrations.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('admin.registrations.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                    </svg>
                                    <span>Verifikasi Pendaftar Baru</span>
                                </div>
                                @if($pendingRegCount > 0)
                                    <span class="px-2 py-0.5 text-[11px] font-bold bg-amber-500 text-white rounded-full">{{ $pendingRegCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('admin.profile-requests.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.profile-requests.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('admin.profile-requests.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                    </svg>
                                    <span>Perubahan Profil & Rekening</span>
                                </div>
                                @if($pendingProfileChangeCount > 0)
                                    <span class="px-2 py-0.5 text-[11px] font-bold bg-amber-500 text-white rounded-full">{{ $pendingProfileChangeCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.reports.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                                <span>Laporan Kinerja Tim</span>
                            </a>

                            <a href="{{ route('admin.payouts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.payouts.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.payouts.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>Pembayaran & Payroll</span>
                            </a>
                        </div>

                        <!-- GRUP: SISTEM -->
                        <div class="space-y-1">
                            <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pengaturan</div>

                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('profile.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('profile.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profil Admin</span>
                            </a>

                            <a href="{{ route('admin.system.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('admin.system.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('admin.system.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Pemeliharaan & Hosting</span>
                            </a>

                            <a href="{{ route('guide') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('guide*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('guide*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>Buku Panduan Sistem</span>
                            </a>
                        </div>

                    @else
                        @php
                            $availableTargetsCount = \App\Models\TargetUrl::available()->count();
                        @endphp

                        <!-- BLOGWALKER MENU -->
                        <div class="space-y-1">
                            <div class="px-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Menu Kerja</div>

                            <a href="{{ route('blogwalker.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('blogwalker.dashboard') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('blogwalker.dashboard') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Dashboard Saya</span>
                            </a>

                            <a href="{{ route('blogwalker.targets.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('blogwalker.targets.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 {{ request()->routeIs('blogwalker.targets.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                    <span>Antrean Target URL</span>
                                </div>
                                @if($availableTargetsCount > 0)
                                    <span class="px-2 py-0.5 text-[11px] font-bold bg-emerald-600 text-white rounded-full">{{ $availableTargetsCount }}</span>
                                @endif
                            </a>

                            <a href="{{ route('blogwalker.submissions.create') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('blogwalker.submissions.create') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('blogwalker.submissions.create') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                <span>+ Kirim Bukti Tugas</span>
                            </a>

                            <a href="{{ route('blogwalker.submissions.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('blogwalker.submissions.index') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('blogwalker.submissions.index') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span>Riwayat Pengerjaan</span>
                            </a>

                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('profile.*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('profile.*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span>Profil & Rekening Saya</span>
                            </a>

                            <a href="{{ route('guide') }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold transition {{ request()->routeIs('guide*') ? 'bg-emerald-50 text-emerald-700 font-bold border-l-4 border-emerald-600' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs('guide*') ? 'text-emerald-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>Panduan Pengerjaan</span>
                            </a>
                        </div>
                    @endif


                </nav>

            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="p-3.5 border-t border-slate-100 bg-slate-50/70 shrink-0">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 min-w-0 hover:opacity-85 transition group" title="Buka Profil & Pengaturan Akun">
                        <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 group-hover:border-emerald-500">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 truncate leading-tight group-hover:text-emerald-700">{{ auth()->user()->name }}</div>
                            <div class="text-[10px] font-semibold uppercase tracking-wider {{ auth()->user()->isAdmin() ? 'text-purple-600' : 'text-emerald-600' }}">
                                {{ auth()->user()->isAdmin() ? 'Super Admin' : 'Blogwalker' }} &bull; <span class="underline">Profil</span>
                            </div>
                        </div>
                    </a>

                    <!-- Quick Logout Button -->
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" title="Keluar / Logout" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>

        </aside>

        <!-- Main Wrapper (Top Header + Page Content + Footer) -->
        <div class="flex-1 min-w-0 flex flex-col min-h-screen">
            
            <!-- Sticky Top Header Bar -->
            <header class="h-16 bg-white border-b border-slate-200/80 px-4 sm:px-6 lg:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
                
                <!-- Left: Mobile toggle & Breadcrumb Title -->
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">
                            @yield('title', 'Dashboard')
                        </h1>
                        <p class="text-[11px] text-slate-400 hidden sm:block">Aplikasi Manajemen Tim Blogwalker</p>
                    </div>
                </div>

                <!-- Right: Date info & User Actions -->
                <div class="flex items-center gap-3">
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 rounded-xl text-xs text-slate-600 font-medium">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>{{ date('d F Y') }}</span>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-semibold text-xs transition" title="Profil & Pengaturan Akun">
                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="hidden sm:inline">Profil</span>
                    </a>

                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.reviews.index') }}" class="relative inline-flex items-center justify-center p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @php
                                $totalPendingReviews = $pendingCount ?? 0;
                            @endphp
                            @if($totalPendingReviews > 0)
                                <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-rose-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </a>
                    @else
                        <a href="{{ route('blogwalker.submissions.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs transition">
                            <span>+ Kirim Bukti</span>
                        </a>
                    @endif
                </div>

            </header>

            <!-- Page Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <!-- Flash Alert Messages -->
                @if(session('success'))
                    <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3 shadow-xs" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 text-emerald-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                        <div class="flex-1 font-medium">{{ session('success') }}</div>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">&times;</button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3 shadow-xs" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                        <div class="flex-1 font-medium">{{ session('error') }}</div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700">&times;</button>
                    </div>
                @endif

                @if(session('warning'))
                    <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm flex items-start gap-3 shadow-xs" x-data="{ show: true }" x-show="show">
                        <svg class="w-5 h-5 text-amber-600 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                        <div class="flex-1 font-medium">{{ session('warning') }}</div>
                        <button @click="show = false" class="text-amber-500 hover:text-amber-700">&times;</button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-sm shadow-xs">
                        <div class="font-bold mb-1 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            Mohon periksa kembali formulir:
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1 text-amber-800">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <!-- Bottom Footer -->
            <footer class="bg-white border-t border-slate-200/80 py-4 text-center text-xs text-slate-400 mt-auto">
                <div class="max-w-7xl mx-auto px-4">
                    &copy; {{ date('Y') }} Blogwalker Pro &bull; Aplikasi Manajemen Tim Link Building &bull; Siap Shared Hosting cPanel
                </div>
            </footer>

        </div>

    </div>

    @else
    <!-- GUEST (LOGIN / REGISTER): CLEAN CENTERED LAYOUT -->
    <div class="min-h-screen flex flex-col justify-between">
        
        <!-- Minimal Guest Header -->
        <header class="h-16 px-6 border-b border-slate-200/80 bg-white flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-base shadow-sm">
                    BW
                </div>
                <span class="font-bold text-slate-900 text-sm">Blogwalker<span class="text-emerald-600">Pro</span></span>
            </div>
            <div class="text-xs text-slate-500">
                @if(request()->routeIs('login'))
                    Belum punya akun? <a href="{{ route('register') }}" class="text-emerald-600 font-bold hover:underline">Daftar Blogwalker &rarr;</a>
                @else
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-emerald-600 font-bold hover:underline">Masuk &rarr;</a>
                @endif
            </div>
        </header>

        <!-- Guest Content -->
        <main class="flex-1 flex justify-center items-center py-8 px-4">
            @yield('content')
        </main>

        <!-- Guest Footer -->
        <footer class="py-4 text-center text-xs text-slate-400 bg-white border-t border-slate-100">
            &copy; {{ date('Y') }} Blogwalker Pro &bull; Sistem Manajemen Tim Blogwalking & SEO
        </footer>

    </div>
    @endauth

    @stack('scripts')
</body>
</html>
