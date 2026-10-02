@php
    // $bind (opsional): nama properti di Alpine induk yang dipakai sebagai nilai siswa, mis. 'f.siswa_id'.
    $fieldName = $name ?? 'siswa_id';
    $idProp = isset($bind) ? 'this.'.$bind : 'this.siswaId';
    $idExpr = isset($bind) ? $bind : 'siswaId';
    $siswaOptions = $siswas->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama, 'nis' => (string) $s->nis, 'kelas' => $s->kelas->nama_kelas ?? '-']);
@endphp
<div x-data="{
        query: '',
        siswaId: '{{ old($fieldName, $default ?? '') }}',
        siswas: @js($siswaOptions),
        open: false,
        get filtered() {
            const q = this.query.toLowerCase().trim();
            if (!q) return [];
            return this.siswas.filter(s => s.nama.toLowerCase().includes(q) || s.nis.toLowerCase().includes(q) || s.kelas.toLowerCase().includes(q)).slice(0, 8);
        },
        get siswaLabel() {
            const s = this.siswas.find(x => String(x.id) === String({{ $idProp }}));
            return s ? s.nama + ' (NIS ' + s.nis + ', ' + s.kelas + ')' : '';
        },
        select(s) { {{ $idProp }} = s.id; this.query = ''; this.open = false; },
        hapus() { {{ $idProp }} = ''; this.query = ''; }
     }" class="relative">
    <input type="hidden" name="{{ $fieldName }}" :value="{{ $idExpr }}">
    <div x-show="{{ $idExpr }}" style="display: none;" class="flex items-center justify-between gap-2 rounded-lg border border-emerald-500/40 bg-slate-900 px-3 py-2 text-sm text-slate-100">
        <span x-text="siswaLabel"></span>
        <button type="button" @click="hapus()" class="text-slate-400 hover:text-slate-100" title="Ganti siswa"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div x-show="!{{ $idExpr }}">
        <input type="text" x-model="query" @focus="open = true" @click.outside="open = false" autocomplete="off"
            placeholder="Ketik nama, NIS, atau kelas siswa..."
            class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
        <div x-show="open && query.trim() && !filtered.length" style="display: none;" class="absolute z-20 mt-1 w-full rounded-lg border border-slate-700 bg-slate-900 px-3 py-2 text-sm text-slate-400 shadow-lg">Siswa tidak ditemukan.</div>
        <div x-show="open && filtered.length" style="display: none;" class="absolute z-20 mt-1 w-full max-h-56 overflow-y-auto rounded-lg border border-slate-700 bg-slate-900 shadow-lg">
            <template x-for="s in filtered" :key="s.id">
                <button type="button" @click="select(s)" class="block w-full px-3 py-2 text-left text-sm text-slate-200 hover:bg-slate-800">
                    <span x-text="s.nama"></span> <span class="text-xs text-slate-400" x-text="'· NIS ' + s.nis + ' · ' + s.kelas"></span>
                </button>
            </template>
        </div>
    </div>
</div>