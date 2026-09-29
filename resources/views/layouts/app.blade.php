<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Blogwalker Pro') }}</title>
    
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
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col font-sans">

    <!-- Top Navigation Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center space-x-3">
                    <a href="{{ auth()->check() && auth()->user()->isAdmin() ? route('admin.dashboard') : route('blogwalker.dashboard') }}" class="flex items-center gap-2 group">
                        <div class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-lg shadow-sm group-hover:bg-emerald-700 transition">
                            BW
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-900 leading-tight tracking-tight">Blogwalker<span class="text-emerald-600">Pro</span></span>
                            <span class="text-[11px] text-slate-500 font-medium">Link Building Team Management</span>
                        </div>
                    </a>

                    <!-- Navigation Links based on Role -->
                    @auth
                    <nav class="hidden md:flex ml-8 space-x-1">
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.reviews.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.reviews.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} relative">
                                Verifikasi
                                @php
                                    $pendingCount = \App\Models\Submission::where('review_status', 'pending')->count();
                                @endphp
                                @if($pendingCount > 0)
                                    <span class="ml-1.5 px-1.5 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.workers.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.workers.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Blogwalker & Plotting
                            </a>
                            <a href="{{ route('admin.registrations.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.registrations.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} relative">
                                Pendaftaran
                                @php
                                    $pendingRegCount = \App\Models\User::where('role', 'blogwalker')->where('approval_status', 'pending')->count();
                                @endphp
                                @if($pendingRegCount > 0)
                                    <span class="ml-1.5 px-1.5 py-0.5 text-xs font-bold bg-amber-500 text-white rounded-full">{{ $pendingRegCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.periods.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.periods.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Target & Periode
                            </a>
                            <a href="{{ route('admin.reports.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Laporan Kinerja
                            </a>
                            <a href="{{ route('admin.targets.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.targets.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} relative">
                                Target URL
                                @php
                                    $availableTargetsCount = \App\Models\TargetUrl::available()->count();
                                @endphp
                                @if($availableTargetsCount > 0)
                                    <span class="ml-1.5 px-1.5 py-0.5 text-xs font-bold bg-emerald-600 text-white rounded-full">{{ $availableTargetsCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.domains.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.domains.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Master Domain
                            </a>
                            <a href="{{ route('admin.payouts.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.payouts.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Rekap Gaji
                            </a>
                            <a href="{{ route('admin.system.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('admin.system.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Sistem
                            </a>
                        @else
                            <a href="{{ route('blogwalker.dashboard') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('blogwalker.dashboard') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('blogwalker.targets.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('blogwalker.targets.*') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }} relative">
                                Antrean Target
                                @php
                                    $availableTargetsCount = \App\Models\TargetUrl::available()->count();
                                @endphp
                                @if($availableTargetsCount > 0)
                                    <span class="ml-1.5 px-1.5 py-0.5 text-xs font-bold bg-emerald-600 text-white rounded-full">{{ $availableTargetsCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('blogwalker.submissions.create') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('blogwalker.submissions.create') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                + Kirim Manual
                            </a>
                            <a href="{{ route('blogwalker.submissions.index') }}" class="px-3 py-2 rounded-md text-sm font-medium {{ request()->routeIs('blogwalker.submissions.index') ? 'bg-emerald-50 text-emerald-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100' }}">
                                Riwayat Saya
                            </a>
                        @endif
                    </nav>
                    @endauth
                </div>

                <!-- User Profile & Action -->
                @auth
                <div class="flex items-center space-x-3" x-data="{ open: false }">
                    <div class="hidden sm:flex flex-col text-right">
                        <span class="text-sm font-semibold text-slate-800 leading-none">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-slate-500 mt-1 uppercase font-medium">
                            <span class="inline-block w-2 h-2 rounded-full {{ auth()->user()->isAdmin() ? 'bg-purple-500' : 'bg-emerald-500' }} mr-1"></span>
                            {{ auth()->user()->role }}
                        </span>
                    </div>

                    <div class="relative">
                        <button @click="open = !open" class="flex items-center justify-center w-9 h-9 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-300 font-semibold text-sm transition">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" @click.outside="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-100 py-1 text-sm z-50">
                            <div class="px-4 py-2 border-b border-slate-100 sm:hidden">
                                <div class="font-medium text-slate-800">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-slate-500">{{ auth()->user()->email }}</div>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-rose-600 hover:bg-rose-50 font-medium transition flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Keluar / Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile Navigation bar -->
        @auth
        <div class="md:hidden border-t border-slate-200 px-4 py-2 flex overflow-x-auto space-x-2 text-xs font-medium bg-slate-50">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.dashboard') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Dashboard</a>
                <a href="{{ route('admin.reviews.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.reviews.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Verifikasi</a>
                <a href="{{ route('admin.periods.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.periods.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Target & Periode</a>
                <a href="{{ route('admin.reports.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.reports.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Laporan</a>
                <a href="{{ route('admin.targets.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.targets.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Target URL</a>
                <a href="{{ route('admin.workers.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.workers.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Blogwalker</a>
                <a href="{{ route('admin.registrations.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.registrations.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Pendaftaran</a>
                <a href="{{ route('admin.domains.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.domains.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Domain</a>
                <a href="{{ route('admin.payouts.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.payouts.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Gaji</a>
                <a href="{{ route('admin.system.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('admin.system.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Sistem</a>
            @else
                <a href="{{ route('blogwalker.dashboard') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('blogwalker.dashboard') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Dashboard</a>
                <a href="{{ route('blogwalker.targets.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('blogwalker.targets.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Antrean Target</a>
                <a href="{{ route('blogwalker.submissions.create') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('blogwalker.submissions.create') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">+ Submit</a>
                <a href="{{ route('blogwalker.submissions.index') }}" class="px-2.5 py-1.5 rounded-md whitespace-nowrap {{ request()->routeIs('blogwalker.submissions.index') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-600 hover:bg-white' }}">Riwayat</a>
            @endif
        </div>
        @endauth
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
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

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            &copy; {{ date('Y') }} Blogwalker Pro &bull; Aplikasi Manajemen Tim Link Building &bull; Siap Shared Hosting cPanel
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
