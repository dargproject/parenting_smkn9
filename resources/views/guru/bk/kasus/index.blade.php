@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h4 class="font-bold text-slate-100 m-0 text-xl">Kasus BK</h4>
            <p class="text-slate-400 text-sm m-0">Daftar kasus bimbingan konseling yang Anda tangani. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <button type="button" onclick="document.getElementById('modal-kasus-baru').style.display='flex'" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-plus mr-1"></i> Kasus Baru</button>
    </div>

    @include('admin.partials.flash')

    <form method="GET" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="lg:col-span-2">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa / judul kasus..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
            </div>
            <select name="status" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Status</option>
                @foreach(['antrean' => 'Antrean', 'proses' => 'Proses', 'selesai' => 'Selesai'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="prioritas" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Prioritas</option>
                @foreach(['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('prioritas') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="kelas_id" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Kelas</option>
                @foreach($kelasOptions as $id => $nama)
                    <option value="{{ $id }}" @selected((string) request('kelas_id') === (string) $id)>{{ $nama }}</option>
                @endforeach
            </select>
            <select name="jurusan" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Jurusan</option>
                @foreach($jurusanOptions as $jurusan)
                    <option value="{{ $jurusan }}" @selected(request('jurusan') === $jurusan)>{{ $jurusan }}</option>
                @endforeach
            </select>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Terapkan Filter</button>
            @if(request()->query())
                <a href="{{ route('guru.bk.kasus.index') }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300">Reset</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-700/60 bg-slate-800/80">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-4 py-3">Siswa</th>
                    <th class="px-4 py-3">Judul</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3 text-center">Prioritas</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3">Tanggal Mulai</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($kasusBks as $kasus)
                    <tr>
                        <td class="px-4 py-3 font-semibold text-slate-100">{{ $kasus->siswa->nama ?? '-' }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">{{ $kasus->siswa->kelas->nama_kelas ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-slate-300">{{ $kasus->judul }}</td>
                        <td class="px-4 py-3 text-slate-400">{{ $kasus->kategoriKasus->nama_kategori ?? $kasus->kategori }}</td>
                        <td class="px-4 py-3 text-center">
                            @php $prioritasBadge = ['rendah' => 'bg-slate-500/10 text-slate-300', 'sedang' => 'bg-amber-500/10 text-amber-400', 'tinggi' => 'bg-rose-500/10 text-rose-400'][$kasus->prioritas] ?? 'bg-slate-500/10 text-slate-300'; @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $prioritasBadge }}">{{ ucfirst($kasus->prioritas) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $statusBadge = ['antrean' => 'bg-rose-500/10 text-rose-400', 'proses' => 'bg-blue-500/10 text-blue-400', 'selesai' => 'bg-emerald-500/10 text-emerald-400'][$kasus->status]; @endphp
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusBadge }}">{{ ucfirst($kasus->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ optional($kasus->tanggal_mulai)->translatedFormat('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('guru.bk.kasus.show', $kasus) }}" class="text-blue-400 hover:text-blue-300 text-sm font-semibold">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">{{ request()->query() ? 'Tidak ada kasus yang cocok dengan filter.' : 'Belum ada kasus BK yang dicatat.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-400 m-0">@if($kasusBks->total()) Menampilkan {{ $kasusBks->firstItem() }}–{{ $kasusBks->lastItem() }} dari {{ $kasusBks->total() }} kasus @endif</p>
        {{ $kasusBks->withQueryString()->links() }}
    </div>
</div>

@include('guru.bk.kasus._form-modal', ['formId' => 'modal-kasus-baru', 'siswas' => $siswas, 'kategoriKasusList' => $kategoriKasusList])
@endsection
