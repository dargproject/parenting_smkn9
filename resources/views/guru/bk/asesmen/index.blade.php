@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">Asesmen BK</h4>
        <p class="text-slate-400 text-sm m-0">Kelola data asesmen diagnostik peserta didik: AKPD, DCM, Gaya Belajar, Sosiometri, dan Tes Bakat Minat. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
    </div>

    @include('admin.partials.flash')

    @if(!$tingkat)
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @foreach(['X', 'XI', 'XII'] as $t)
                <a href="{{ route('guru.bk.asesmen.index', ['tingkat' => $t]) }}" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-6 hover:border-blue-500 transition-colors">
                    <p class="text-xs uppercase text-slate-500 font-bold">Tingkat</p>
                    <h3 class="text-xl font-bold text-slate-100 mt-1">Kelas {{ $t }}</h3>
                    <p class="text-sm text-blue-400 mt-4">Lihat Data Siswa <i class="fa-solid fa-arrow-right ml-1"></i></p>
                </a>
            @endforeach
        </div>
    @else
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 overflow-hidden">
            <div class="p-5 border-b border-slate-700/60 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <a href="{{ route('guru.bk.asesmen.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Pilih Tingkat</a>
                    <h5 class="font-bold text-slate-100 m-0 mt-1">Asesmen Kelas {{ $tingkat }} <span class="text-slate-400 font-normal">({{ $siswas->count() }} siswa)</span></h5>
                </div>
                <form method="GET" class="flex flex-wrap gap-2">
                    <input type="hidden" name="tingkat" value="{{ $tingkat }}">
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa..." class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                    <select name="kelas_id" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="">Semua Kelas</option>
                        @foreach($kelasOptions as $id => $nama)
                            <option value="{{ $id }}" @selected((string) request('kelas_id') === (string) $id)>{{ $nama }}</option>
                        @endforeach
                    </select>
                    <select name="jurusan" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusanOptions as $jurusan)
                            <option value="{{ $jurusan }}" @selected(request('jurusan') === $jurusan)>{{ $jurusan }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Filter</button>
                </form>
            </div>

            <table class="w-full min-w-[760px] text-left text-sm">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                    <tr>
                        <th class="px-4 py-3">Siswa</th>
                        <th class="px-4 py-3">Kelas</th>
                        <th class="px-3 py-3 text-center">AKPD</th>
                        <th class="px-3 py-3 text-center">DCM</th>
                        <th class="px-3 py-3 text-center">Gaya Belajar</th>
                        <th class="px-3 py-3 text-center">Sosiometri</th>
                        <th class="px-3 py-3 text-center">Tes Bakat Minat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($siswas as $siswa)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-100">{{ $siswa->nama }}<span class="block text-slate-400 font-normal" style="font-size: 10px;">NIS {{ $siswa->nis }}</span></td>
                            <td class="px-4 py-3 text-slate-400">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('guru.bk.asesmen.akpd.index', ['search' => $siswa->nama]) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-400 hover:bg-emerald-500/20">AKPD</a>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('guru.bk.asesmen.dcm.index', ['search' => $siswa->nama]) }}" class="inline-flex items-center gap-1 rounded-lg bg-blue-500/10 px-2.5 py-1 text-xs font-semibold text-blue-400 hover:bg-blue-500/20">DCM</a>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('guru.bk.asesmen.gaya-belajar.index', ['search' => $siswa->nama]) }}" class="inline-flex items-center gap-1 rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400 hover:bg-amber-500/20">Gaya Belajar</a>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('guru.bk.asesmen.sosiometri.index', ['search' => $siswa->nama]) }}" class="inline-flex items-center gap-1 rounded-lg bg-purple-500/10 px-2.5 py-1 text-xs font-semibold text-purple-400 hover:bg-purple-500/20">Sosiometri</a>
                            </td>
                            <td class="px-3 py-3 text-center">
                                <a href="{{ route('guru.bk.asesmen.peminatan.index', ['search' => $siswa->nama]) }}" class="inline-flex items-center gap-1 rounded-lg bg-rose-500/10 px-2.5 py-1 text-xs font-semibold text-rose-400 hover:bg-rose-500/20">Bakat Minat</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada data siswa untuk kelas {{ $tingkat }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
