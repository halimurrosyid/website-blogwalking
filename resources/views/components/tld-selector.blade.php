@props([
    'name' => 'allowed_tlds',
    'id' => 'allowed_tlds',
    'value' => '',
])

@php
    $initialValue = is_array($value) ? implode(', ', $value) : (string) $value;
    $groupedCatalog = \App\Services\TldCatalogService::groupedCatalog();
@endphp

<div x-data="{
    selectedTlds: [],
    customInput: '',
    searchQuery: '',
    selectedCategory: 'all',
    catalog: @js($groupedCatalog),
    init() {
        const raw = @js($initialValue);
        if (raw && typeof raw === 'string') {
            this.selectedTlds = raw.split(',')
                .map(item => item.trim())
                .filter(item => item.length > 0)
                .map(item => item.startsWith('.') ? item.toLowerCase() : '.' + item.toLowerCase());
        }
    },
    get tldsString() {
        return this.selectedTlds.join(', ');
    },
    addTld(tld) {
        if (!tld) return;
        let clean = tld.trim().toLowerCase();
        if (!clean.startsWith('.')) clean = '.' + clean;
        if (!this.selectedTlds.includes(clean)) {
            this.selectedTlds.push(clean);
        }
    },
    removeTld(tld) {
        this.selectedTlds = this.selectedTlds.filter(item => item !== tld);
    },
    toggleTld(tld) {
        if (this.selectedTlds.includes(tld)) {
            this.removeTld(tld);
        } else {
            this.addTld(tld);
        }
    },
    addCustom() {
        if (!this.customInput.trim()) return;
        const items = this.customInput.split(',');
        items.forEach(i => this.addTld(i));
        this.customInput = '';
    },
    applyPreset(type) {
        if (type === 'all') {
            this.selectedTlds = [];
        } else if (type === 'id_all') {
            this.selectedTlds = ['.id', '.co.id', '.web.id', '.my.id', '.biz.id'];
        } else if (type === 'id_coid') {
            this.selectedTlds = ['.co.id'];
        } else if (type === 'global') {
            this.selectedTlds = ['.com', '.net', '.org', '.info'];
        } else if (type === 'tech') {
            this.selectedTlds = ['.ai', '.io', '.dev', '.app', '.tech'];
        } else if (type === 'shop') {
            this.selectedTlds = ['.shop', '.store', '.market'];
        } else if (type === 'edu') {
            this.selectedTlds = ['.ac.id', '.sch.id', '.edu'];
        }
    }
}" class="space-y-3">

    <!-- Hidden input bound to form submission -->
    <input type="hidden" name="{{ $name }}" id="{{ $id }}" :value="tldsString">

    <!-- Preset Cepat Buttons -->
    <div>
        <div class="flex flex-wrap items-center gap-1.5 mb-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mr-1">Preset Cepat:</span>
            
            <button type="button" @click="applyPreset('all')"
                :class="selectedTlds.length === 0 ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>✨ Bebas Semua Domain</span>
            </button>

            <button type="button" @click="applyPreset('id_all')"
                :class="selectedTlds.includes('.id') && selectedTlds.includes('.co.id') ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>🇮🇩 Semua .ID</span>
            </button>

            <button type="button" @click="applyPreset('id_coid')"
                :class="selectedTlds.length === 1 && selectedTlds.includes('.co.id') ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>🏢 Khusus .co.id</span>
            </button>

            <button type="button" @click="applyPreset('global')"
                :class="selectedTlds.includes('.com') && selectedTlds.includes('.net') ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>🌐 Global (.com, .net, dll)</span>
            </button>

            <button type="button" @click="applyPreset('tech')"
                :class="selectedTlds.includes('.ai') && selectedTlds.includes('.io') ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>💻 Tech & AI (.ai, .io, .dev)</span>
            </button>

            <button type="button" @click="applyPreset('shop')"
                :class="selectedTlds.includes('.shop') ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>🛍️ E-Commerce (.shop, .store)</span>
            </button>

            <button type="button" @click="applyPreset('edu')"
                :class="selectedTlds.includes('.ac.id') && selectedTlds.includes('.edu') ? 'bg-emerald-600 text-white font-bold border-emerald-600 shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300'"
                class="px-2.5 py-1 rounded-lg border text-xs transition cursor-pointer flex items-center gap-1">
                <span>🎓 Edukasi (.ac.id, .edu)</span>
            </button>
        </div>
    </div>

    <!-- Dropdown Selector & Custom Input Row -->
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
        <div class="sm:col-span-8">
            <select @change="if ($event.target.value) { addTld($event.target.value); $event.target.value = ''; }"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 bg-white focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs sm:text-sm font-medium transition cursor-pointer shadow-2xs">
                <option value="">➕ Pilih Ekstensi Domain Seluruh Dunia dari Dropdown (230+ TLDs)...</option>
                @foreach ($groupedCatalog as $groupName => $options)
                    <optgroup label="{{ $groupName }}">
                        @foreach ($options as $ext => $label)
                            <option value="{{ $ext }}">{{ $label }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-4 flex items-center gap-1.5">
            <input type="text" x-model="customInput" @keydown.enter.prevent="addCustom()"
                placeholder="TLD lain: .store, .asia"
                class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-xs font-mono">
            <button type="button" @click="addCustom()"
                class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 font-bold text-xs shrink-0 transition cursor-pointer">
                + Tambah
            </button>
        </div>
    </div>

    <!-- Display Active Selected TLD Tags -->
    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 min-h-[52px] flex flex-wrap items-center gap-2">
        <template x-if="selectedTlds.length === 0">
            <div class="flex items-center gap-2 text-xs text-emerald-700 font-semibold">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                <span>Bebas Semua Ekstensi Domain (Worker ini boleh berkomentar di website domain mana pun di seluruh dunia tanpa batasan TLD).</span>
            </div>
        </template>

        <template x-if="selectedTlds.length > 0">
            <div class="flex flex-wrap items-center gap-2 w-full">
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mr-1">
                    Aktif Terpilih (<span x-text="selectedTlds.length"></span>):
                </span>

                <template x-for="tld in selectedTlds" :key="tld">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-mono font-bold bg-emerald-100 text-emerald-900 border border-emerald-300 shadow-2xs">
                        <span x-text="tld"></span>
                        <button type="button" @click="removeTld(tld)" class="text-emerald-700 hover:text-rose-600 font-bold ml-1 transition cursor-pointer text-sm leading-none" title="Hapus ekstensi ini">&times;</button>
                    </span>
                </template>

                <button type="button" @click="selectedTlds = []" class="text-[11px] text-rose-600 hover:text-rose-800 underline font-bold ml-auto transition cursor-pointer">
                    Hapus Semua / Reset Bebas
                </button>
            </div>
        </template>
    </div>

</div>
