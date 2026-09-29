@extends('layouts.app')

@section('title', 'Kirim Bukti Komentar')

@section('content')
<div class="max-w-2xl mx-auto py-4">

    <!-- Header -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Form Pengiriman Bukti Komentar</h1>
            <p class="text-sm text-slate-500 mt-1">Isi URL artikel yang dikomentari dan lampirkan bukti screenshot.</p>
        </div>
        <a href="{{ route('blogwalker.dashboard') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-2 rounded-lg bg-white border border-slate-200">
            &larr; Kembali
        </a>
    </div>

    <!-- Active Target URL Task Banner -->
    @if(isset($target) && $target)
    <div class="mb-6 p-5 rounded-2xl bg-emerald-900 text-white shadow-md border border-emerald-700/50 space-y-2">
        <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Tugas Antrean Target
            </span>
            <a href="{{ $target->url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 px-3 py-1 bg-emerald-700 hover:bg-emerald-600 rounded-lg text-xs font-semibold text-white transition">
                <span>Buka Website Target</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
            </a>
        </div>
        <div class="text-sm font-semibold text-white break-all">
            {{ $target->url }}
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-emerald-200/90 pt-1 border-t border-emerald-800">
            @if($target->keyword)
                <div>Keyword Target: <strong class="text-white">{{ $target->keyword }}</strong></div>
            @endif
            @if($target->notes)
                <div>Instruksi: <span class="italic text-emerald-100">{{ $target->notes }}</span></div>
            @endif
            <div>Domain: <strong class="font-mono text-white">{{ $target->root_domain }}</strong></div>
        </div>
    </div>
    @endif

    <!-- Active Reminder Banner -->
    @if($assignment && !isset($target))
    <div class="mb-6 p-4 rounded-xl bg-slate-900 text-white text-xs space-y-1">
        <div class="font-bold text-emerald-400 uppercase tracking-wider">Target Anda:</div>
        <div>
            Domain: <span class="font-bold text-white">{{ !empty($assignment->allowed_tlds) ? implode(', ', $assignment->allowed_tlds) : 'Bebas Semua Domain (.id, .com, dll.)' }}</span> &bull; 
            Keyword: <span class="font-bold text-white">{{ $assignment->target_keywords }}</span>
        </div>
        <div class="truncate">
            Target Link: <a href="{{ $assignment->target_backlink_url }}" target="_blank" class="text-emerald-300 underline font-mono">{{ $assignment->target_backlink_url }}</a>
        </div>
    </div>
    @endif

    <!-- Submission Form -->
    <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-200 shadow-sm" x-data="screenshotUploader()">
        <form method="POST" action="{{ route('blogwalker.submissions.store') }}" enctype="multipart/form-data" @submit="handleSubmit($event)" class="space-y-6">
            @csrf

            @if(isset($target) && $target)
                <input type="hidden" name="target_id" value="{{ $target->id }}">
            @endif

            <!-- Target URL -->
            <div>
                <label for="target_url" class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-1.5">
                    URL Artikel Blog yang Dikomentari <span class="text-rose-500">*</span>
                </label>
                <input type="url" name="target_url" id="target_url" x-model="targetUrl" @input.debounce.500ms="validateDomain()"
                    value="{{ old('target_url', isset($target) ? $target->url : request('prefill')) }}" {{ isset($target) ? 'readonly' : '' }} required
                    class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm transition {{ isset($target) ? 'bg-slate-50 font-mono text-slate-700' : '' }}"
                    placeholder="https://contoh-blog.co.id/artikel/belajar-seo">
                <span class="text-[11px] text-slate-400 mt-1 block">
                    {{ isset($target) ? 'URL otomatis terkunci dari antrean target pengerjaan.' : 'Masukkan alamat lengkap halaman website/artikel tempat Anda menaruh komentar.' }}
                </span>

                <!-- Realtime Domain Status Feedback -->
                <div x-show="domainFeedback" x-cloak class="mt-2 text-xs font-medium px-3 py-2 rounded-lg transition"
                    :class="domainCanSubmit ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
                    <span x-text="domainFeedback"></span>
                </div>
            </div>

            <!-- Comment Type -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700 mb-2">
                    Status Tayang Komentar <span class="text-rose-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 bg-slate-50/50 hover:bg-emerald-50/30 cursor-pointer transition">
                        <input type="radio" name="comment_type" value="approved_live" checked class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-2.5">
                            <span class="block text-xs font-bold text-slate-800">Approved / Live Langsung</span>
                            <span class="block text-[11px] text-slate-500">Komentar langsung muncul di halaman</span>
                        </div>
                    </label>
                    <label class="flex items-center p-3.5 rounded-xl border border-slate-200 hover:border-emerald-400 bg-slate-50/50 hover:bg-emerald-50/30 cursor-pointer transition">
                        <input type="radio" name="comment_type" value="pending_moderation" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div class="ml-2.5">
                            <span class="block text-xs font-bold text-slate-800">Menunggu Moderasi</span>
                            <span class="block text-[11px] text-slate-500">Ada tanda "Awaiting moderation"</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- SCREENSHOT UPLOAD WITH CTRL + V SUPPORT -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-700">
                        Bukti Screenshot Komentar <span class="text-rose-500">*</span>
                    </label>
                    <span class="text-[11px] px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold">
                        Bisa Langsung Ctrl + V!
                    </span>
                </div>

                <!-- Hidden inputs to receive processed screenshot -->
                <input type="hidden" name="screenshot_base64" :value="imageBase64">
                <input type="file" id="fileInput" name="screenshot_file" accept="image/*" class="hidden" @change="handleFileSelect($event)">

                <!-- Dropzone / Paste Area -->
                <div 
                    @paste.window="handlePaste($event)"
                    @click="triggerFileInput()"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    :class="{ 'border-emerald-500 bg-emerald-50/40': isDragging || isHovered, 'border-slate-300 bg-slate-50/60': !isDragging && !hasImage }"
                    class="relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition-all hover:border-emerald-400 hover:bg-emerald-50/30"
                >
                    <!-- State 1: No Image Loaded Yet -->
                    <template x-if="!hasImage">
                        <div class="space-y-3 py-4">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 mb-1">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-800">
                                    Tekan <kbd class="px-2 py-1 bg-white border border-slate-300 rounded shadow-xs text-xs font-mono text-emerald-700">Ctrl + V</kbd> untuk Paste Screenshot
                                </div>
                                <div class="text-xs text-slate-500 mt-1">
                                    atau <span class="text-emerald-600 font-semibold hover:underline">klik di sini untuk pilih file gambar</span> dari komputer / HP
                                </div>
                            </div>
                            <div class="text-[11px] text-slate-400">
                                💡 Tip: Tekan tombol <code>Win + Shift + S</code> untuk snipping gambar, lalu langsung tekan <code>Ctrl + V</code> di sini!
                            </div>
                        </div>
                    </template>

                    <!-- State 2: Image Preview Loaded -->
                    <template x-if="hasImage">
                        <div class="space-y-3" @click.stop>
                            <div class="relative inline-block max-w-full rounded-xl overflow-hidden shadow-sm border border-slate-200">
                                <img :src="imagePreviewUrl" alt="Preview Screenshot" class="max-h-72 w-auto mx-auto object-contain bg-slate-900">
                                <div class="absolute top-2 right-2 bg-emerald-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-xs">
                                    Siap Dikirim
                                </div>
                            </div>
                            <div class="flex items-center justify-center gap-2 pt-2">
                                <button type="button" @click="resetImage()" class="px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold transition cursor-pointer">
                                    ✕ Hapus & Paste Ulang
                                </button>
                                <button type="button" @click="triggerFileInput()" class="px-3 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer">
                                    Ganti Gambar File
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button type="submit" :disabled="isSubmitting || (domainFeedback && !domainCanSubmit)"
                    class="w-full py-3.5 px-6 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm shadow-md shadow-emerald-100 hover:shadow-lg transition disabled:opacity-50 cursor-pointer flex items-center justify-center gap-2">
                    <span x-show="!isSubmitting">Kirim Bukti Komentar Sekarang</span>
                    <span x-show="isSubmitting" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Mengompres & Mengirim...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function screenshotUploader() {
    return {
        targetUrl: '{{ old('target_url', isset($target) ? $target->url : request('prefill')) }}',
        hasImage: false,
        imagePreviewUrl: null,
        imageBase64: '',
        isDragging: false,
        isHovered: false,
        isSubmitting: false,
        domainFeedback: null,
        domainCanSubmit: true,

        init() {
            if (this.targetUrl) {
                this.validateDomain();
            }
        },

        triggerFileInput() {
            document.getElementById('fileInput').click();
        },

        handlePaste(e) {
            // Check if clipboard contains image items
            const items = (e.clipboardData || e.originalEvent.clipboardData).items;
            for (let i = 0; i < items.length; i++) {
                if (items[i].type.indexOf('image') !== -1) {
                    const blob = items[i].getAsFile();
                    this.processBlob(blob);
                    e.preventDefault();
                    break;
                }
            }
        },

        handleDrop(e) {
            this.isDragging = false;
            if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                const file = e.dataTransfer.files[0];
                if (file.type.startsWith('image/')) {
                    this.processBlob(file);
                }
            }
        },

        handleFileSelect(e) {
            if (e.target.files && e.target.files[0]) {
                this.processBlob(e.target.files[0]);
            }
        },

        processBlob(blob) {
            const reader = new FileReader();
            reader.onload = (event) => {
                // Compress image via Canvas to reduce upload size by 90%
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const maxWidth = 1600;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxWidth) {
                        height = Math.round((height * maxWidth) / width);
                        width = maxWidth;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Convert to WebP data URL
                    const compressedBase64 = canvas.toDataURL('image/webp', 0.82);
                    this.imageBase64 = compressedBase64;
                    this.imagePreviewUrl = compressedBase64;
                    this.hasImage = true;
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(blob);
        },

        resetImage() {
            this.hasImage = false;
            this.imagePreviewUrl = null;
            this.imageBase64 = '';
            document.getElementById('fileInput').value = '';
        },

        async validateDomain() {
            if (!this.targetUrl || !this.targetUrl.includes('.')) {
                this.domainFeedback = null;
                this.domainCanSubmit = true;
                return;
            }

            try {
                const res = await fetch('{{ route('blogwalker.check-domain') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: this.targetUrl })
                });
                const data = await res.json();
                if (data.success) {
                    this.domainCanSubmit = data.can_submit;
                    this.domainFeedback = data.message;
                }
            } catch (err) {
                // ignore network error on typing
            }
        },

        handleSubmit(e) {
            if (!this.hasImage && !document.getElementById('fileInput').files.length) {
                alert('Silakan lampirkan bukti screenshot terlebih dahulu (bisa paste Ctrl+V).');
                e.preventDefault();
                return;
            }
            if (this.domainFeedback && !this.domainCanSubmit) {
                alert('Domain ini sudah mencapai batas kuota 5 URL. Silakan cari website lain.');
                e.preventDefault();
                return;
            }
            this.isSubmitting = true;
        }
    };
}
</script>
@endpush
