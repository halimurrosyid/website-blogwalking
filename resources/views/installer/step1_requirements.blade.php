@extends('installer.layout', ['step' => 1])

@section('title', 'Langkah 1: Pemeriksaan Server')

@section('installer_content')
<div class="space-y-6">

    <div>
        <h2 class="text-lg font-bold text-slate-900">Pemeriksaan Persyaratan Server</h2>
        <p class="text-xs text-slate-500 mt-1">
            Memeriksa versi PHP, ekstensi penting, dan hak akses direktori penyimpanan di hosting Anda.
        </p>
    </div>

    <!-- Requirements List -->
    <div class="space-y-3">
        <!-- PHP Version -->
        <div class="flex items-center justify-between p-3 rounded-xl border {{ $phpSatisfied ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold {{ $phpSatisfied ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                    {{ $phpSatisfied ? '✓' : '✗' }}
                </span>
                <span class="text-xs font-semibold text-slate-800">Versi PHP (Minimal 8.2)</span>
            </div>
            <span class="text-xs font-mono font-bold {{ $phpSatisfied ? 'text-emerald-700' : 'text-rose-700' }}">
                PHP {{ $phpVersion }}
            </span>
        </div>

        <!-- PHP Extensions -->
        @foreach($extensionResults as $ext => $res)
        <div class="flex items-center justify-between p-3 rounded-xl border {{ $res['satisfied'] ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold {{ $res['satisfied'] ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                    {{ $res['satisfied'] ? '✓' : '✗' }}
                </span>
                <span class="text-xs font-semibold text-slate-800">{{ $res['label'] }}</span>
            </div>
            <span class="text-xs font-bold {{ $res['satisfied'] ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $res['satisfied'] ? 'Terpasang' : 'Belum Aktif' }}
            </span>
        </div>
        @endforeach

        <!-- Directory Permissions -->
        <div class="flex items-center justify-between p-3 rounded-xl border {{ $storageWritable ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold {{ $storageWritable ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                    {{ $storageWritable ? '✓' : '✗' }}
                </span>
                <span class="text-xs font-semibold text-slate-800">Direktori <code class="font-mono text-[11px]">storage/</code> (Writable)</span>
            </div>
            <span class="text-xs font-bold {{ $storageWritable ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $storageWritable ? 'Siap Tulis (775/755)' : 'Izin Ditolak' }}
            </span>
        </div>

        <div class="flex items-center justify-between p-3 rounded-xl border {{ $cacheWritable ? 'bg-emerald-50/50 border-emerald-200' : 'bg-rose-50 border-rose-200' }}">
            <div class="flex items-center gap-2.5">
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold {{ $cacheWritable ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white' }}">
                    {{ $cacheWritable ? '✓' : '✗' }}
                </span>
                <span class="text-xs font-semibold text-slate-800">Direktori <code class="font-mono text-[11px]">bootstrap/cache/</code></span>
            </div>
            <span class="text-xs font-bold {{ $cacheWritable ? 'text-emerald-700' : 'text-rose-700' }}">
                {{ $cacheWritable ? 'Siap Tulis (775/755)' : 'Izin Ditolak' }}
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
        @if($allRequirementsMet)
            <div class="text-xs text-emerald-700 font-semibold flex items-center gap-1.5">
                <span>✓ Server Anda siap menjalankan aplikasi!</span>
            </div>
            <a href="{{ route('install.database') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-100 transition">
                Lanjut ke Konfigurasi Database &rarr;
            </a>
        @else
            <div class="text-xs text-rose-700 font-semibold">
                ⚠️ Mohon lengkapi ekstensi / izin folder di atas sebelum melanjutkan.
            </div>
            <button disabled class="px-5 py-2.5 rounded-xl bg-slate-200 text-slate-400 font-bold text-xs cursor-not-allowed">
                Lanjut ke Database
            </button>
        @endif
    </div>

</div>
@endsection
