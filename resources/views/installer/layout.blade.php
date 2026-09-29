<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Instalasi Aplikasi') - Blogwalker Pro Installer</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.14.3/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased min-h-screen flex flex-col justify-center py-10 px-4 sm:px-6 lg:px-8 font-sans">

    <div class="max-w-xl w-full mx-auto space-y-6">

        <!-- Logo & Title -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-emerald-600 text-white font-bold text-2xl mb-2 shadow-md shadow-emerald-200">
                BW
            </div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Blogwalker<span class="text-emerald-600">Pro</span></h1>
            <p class="text-xs text-slate-500 font-medium">Panduan Instalasi & Pengaturan Awal Sistem</p>
        </div>

        <!-- Wizard Step Indicator -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs">
            <div class="flex items-center justify-between text-xs font-bold text-slate-400">
                @php
                    $currentStep = $step ?? 1;
                @endphp
                <div class="flex items-center gap-1.5 {{ $currentStep >= 1 ? 'text-emerald-600' : '' }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 1 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">1</span>
                    <span class="hidden sm:inline">Cek Server</span>
                </div>
                <div class="w-6 h-0.5 {{ $currentStep >= 2 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                <div class="flex items-center gap-1.5 {{ $currentStep >= 2 ? 'text-emerald-600' : '' }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 2 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">2</span>
                    <span class="hidden sm:inline">Database</span>
                </div>
                <div class="w-6 h-0.5 {{ $currentStep >= 3 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                <div class="flex items-center gap-1.5 {{ $currentStep >= 3 ? 'text-emerald-600' : '' }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 3 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">3</span>
                    <span class="hidden sm:inline">Akun Admin</span>
                </div>
                <div class="w-6 h-0.5 {{ $currentStep >= 4 ? 'bg-emerald-500' : 'bg-slate-200' }}"></div>

                <div class="flex items-center gap-1.5 {{ $currentStep >= 4 ? 'text-emerald-600' : '' }}">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold {{ $currentStep >= 4 ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-400' }}">4</span>
                    <span class="hidden sm:inline">Selesai</span>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center gap-2">
                <span>✓</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs">
                <div class="font-bold mb-1">Periksa kendala berikut:</div>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Step Content Card -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm">
            @yield('installer_content')
        </div>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Blogwalker Pro Installer &bull; Siap cPanel Shared Hosting
        </div>

    </div>

</body>
</html>
