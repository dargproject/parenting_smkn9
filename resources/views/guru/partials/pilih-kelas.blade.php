{{-- Butuh Alpine scope induk dengan state `kelas` dan `cari` (lihat kelasXData di layouts/app). --}}
<div class="flex flex-col gap-2 sm:flex-row sm:items-center">
    <div class="flex items-center gap-2">
        <label class="text-sm font-medium text-slate-400 whitespace-nowrap"><i class="fa-solid fa-people-roof mr-1"></i> Kelas</label>
        <select x-model="kelas" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-1.5 text-sm text-slate-100">
            @foreach($daftarKelas as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}@isset($jumlahSiswa[$k->id]) ({{ $jumlahSiswa[$k->id] }} siswa)@endisset</option>
            @endforeach
        </select>
    </div>
    @if($cari ?? false)
        <div class="relative flex-1 sm:max-w-xs">
            <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            <input type="search" x-model="cari" placeholder="Cari nama siswa..." class="w-full rounded-lg border border-slate-600 bg-slate-900 py-1.5 pl-9 pr-3 text-sm text-slate-100 placeholder:text-slate-500">
        </div>
    @endif
</div>