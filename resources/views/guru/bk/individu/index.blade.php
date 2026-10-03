@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h4 class="font-bold text-slate-100 m-0 text-xl">Konseling Individu</h4>
            <p class="text-slate-400 text-sm m-0">Riwayat layanan konseling individu yang Anda catat. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <button type="button" onclick="document.getElementById('modal-individu-baru').style.display='flex'" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500"><i class="fa-solid fa-plus mr-1"></i> Layanan Baru</button>
    </div>

    @include('admin.partials.flash')

    <form method="GET" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <div class="lg:col-span-2">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa / uraian masalah..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
            </div>
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
            <select name="jenis_kelamin" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Semua Jenis Kelamin</option>
                <option value="L" @selected(request('jenis_kelamin') === 'L')>Laki-laki</option>
                <option value="P" @selected(request('jenis_kelamin') === 'P')>Perempuan</option>
            </select>
        </div>
        <div class="mt-3 flex items-center gap-2">
            <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Terapkan Filter</button>
            @if(request()->query())
                <a href="{{ route('guru.bk.individu.index') }}" class="rounded-lg border border-slate-600 px-4 py-2 text-sm text-slate-300">Reset</a>
            @endif
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-700/60 bg-slate-800/80">
        <table class="w-full min-w-[720px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Siswa</th>
                    <th class="px-4 py-3 text-center">J. Kelamin</th>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="px-4 py-3">Uraian Masalah</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($records as $record)
                    @php $siswa = $record->kasusBk->siswa ?? null; @endphp
                    <tr>
                        <td class="px-4 py-3 text-slate-300 whitespace-nowrap">{{ $record->tanggal_layanan->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-100">{{ $siswa->nama ?? '-' }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">NIS {{ $siswa->nis ?? '-' }}</span></td>
                        <td class="px-4 py-3 text-center">
                            @if(($siswa->jenis_kelamin ?? null) === 'L')
                                <span class="rounded-full bg-blue-500/10 px-2 py-0.5 text-xs text-blue-400">Laki-laki</span>
                            @elseif(($siswa->jenis_kelamin ?? null) === 'P')
                                <span class="rounded-full bg-pink-500/10 px-2 py-0.5 text-xs text-pink-400">Perempuan</span>
                            @else
                                <span class="text-slate-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-400">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-300 max-w-xs truncate">{{ $record->kasusBk->deskripsi ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('guru.bk.individu.show', $record) }}" class="text-blue-400 hover:text-blue-300 text-sm font-semibold">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-400">{{ request()->query() ? 'Tidak ada layanan yang cocok dengan filter.' : 'Belum ada layanan konseling individu yang dicatat.' }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex items-center justify-between">
        <p class="text-sm text-slate-400 m-0">@if($records->total()) Menampilkan {{ $records->firstItem() }}–{{ $records->lastItem() }} dari {{ $records->total() }} layanan @endif</p>
        {{ $records->withQueryString()->links() }}
    </div>
</div>

@include('guru.bk.individu._form-modal', ['formId' => 'modal-individu-baru', 'siswas' => $siswas])
@endsection
