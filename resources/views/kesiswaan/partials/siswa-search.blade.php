@php
    $fieldName = $name ?? 'siswa_id';
    $siswaOptions = $siswas->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama, 'nis' => $s->nis, 'kelas' => $s->kelas->nama_kelas ?? '-']);
@endphp
<div x-data="{
        query: '',
        siswaId: '{{ old($fieldName, '') }}',
        siswaLabel: '',
        siswas: @js($siswaOptions),
        open: false,
        get filtered() {
            if (this.query.length < 1) return [];
            const q = this.query.toLowerCase();
            return this.siswas.filter(s => s.nama.toLowerCase().includes(q) || s.nis.toLowerCase().includes(q)).slice(0, 8);
        },
        select(s) { this.siswaId = s.id; this.siswaLabel = s.nama + ' (NIS ' + s.nis + ')'; this.query = ''; this.open = false; }
     }" class="relative">
    <input type="hidden" name="{{ $fieldName }}" :value="siswaId" required>
    <input type="text" x-model="query" @focus="open = true" @click.outside="open = false"
        :placeholder="siswaLabel ? siswaLabel : 'Cari nama atau NIS siswa...'"
        class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
    <div x-show="open && filtered.length" style="display: none;" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 shadow-lg max-h-56 overflow-y-auto">
        <template x-for="s in filtered" :key="s.id">
            <button type="button" @click="select(s)" class="block w-full text-left px-3 py-2 text-sm text-slate-200 hover:bg-slate-800">
                <span x-text="s.nama"></span> <span class="text-slate-400 text-xs" x-text="'· NIS ' + s.nis + ' · ' + s.kelas"></span>
            </button>
        </template>
    </div>
    <p x-show="siswaId" style="display: none;" class="mt-1 text-xs text-emerald-400" x-text="siswaLabel ? 'Terpilih: ' + siswaLabel : ''"></p>
</div>
