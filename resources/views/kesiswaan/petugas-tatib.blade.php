@php
    $calonPetugas = \App\Models\Guru::with('roles')
        ->whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'kepsek']))
        ->orderBy('nama')->get(['id', 'nama', 'nip']);
    $petugasIds = $calonPetugas->filter(fn ($g) => $g->roles->contains('name', 'tatib'))->pluck('id');
@endphp
<div id="pane-kesiswaan-petugas" class="pane-content hidden-pane fade-transition" x-data="{ cari: '', hanyaPetugas: false, dipilih: @js($petugasIds->values()) }">
    <div class="mb-3">
        <h4 class="font-bold text-slate-100 m-0">Petugas Tatib</h4>
        <p class="text-slate-400 small m-0">Guru yang dicentang dapat mencatat pelanggaran dan poin siswa. Peran ini juga bisa diatur admin di menu Guru &amp; Staf.</p>
    </div>

    <form method="POST" action="{{ route('guru.kesiswaan.petugas-tatib.update') }}" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 overflow-hidden">
        @csrf
        <div class="flex flex-col gap-2 border-b border-slate-700/60 p-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input type="search" x-model="cari" placeholder="Cari nama atau NIP guru..." class="w-full rounded-lg border border-slate-600 bg-slate-900 py-1.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500">
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-300 cursor-pointer"><input type="checkbox" x-model="hanyaPetugas" class="h-4 w-4"> Hanya petugas</label>
            <span class="text-xs text-slate-400" x-text="dipilih.length + ' petugas'"></span>
        </div>
        <div class="max-h-[55vh] overflow-y-auto">
            <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-slate-700/60">
                    @foreach($calonPetugas as $g)
                        <tr x-show="(!hanyaPetugas || dipilih.includes({{ $g->id }})) && (!cari || {{ \Illuminate\Support\Js::from(mb_strtolower($g->nama.' '.$g->nip)) }}.includes(cari.toLowerCase()))">
                            <td class="px-3 py-2 w-10"><input type="checkbox" name="guru_ids[]" value="{{ $g->id }}" x-model.number="dipilih" class="h-4 w-4"></td>
                            <td class="px-3 py-2 font-semibold text-slate-100">{{ $g->nama }}</td>
                            {{-- <td class="px-3 py-2 text-xs text-slate-400">{{ $g->nip }}</td> --}}
                            <td class="px-3 py-2 text-xs text-slate-400">{{ $g->roles->pluck('name')->reject(fn ($n) => $n === 'tatib')->map(fn ($n) => str_replace('_', ' ', $n))->implode(', ') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="flex justify-end border-t border-slate-700/60 bg-slate-900 px-3 py-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-1.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Petugas Tatib</button>
        </div>
    </form>
</div>
