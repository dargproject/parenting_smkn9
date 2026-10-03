@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">Rujukan Masuk</h4>
        <p class="text-slate-400 text-sm m-0">Rujukan siswa dari wali kelas yang menunggu ditinjau. Menerima akan otomatis membuat Kasus BK baru dengan Anda sebagai konselor. <span class="confidential-badge"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
    </div>

    @include('admin.partials.flash')

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
        <h6 class="font-bold text-slate-100 mb-3">Menunggu Ditinjau ({{ $menunggu->count() }})</h6>
        <div class="flex flex-col gap-3">
            @forelse($menunggu as $rujukan)
                <div class="rounded-xl border border-slate-700/60 bg-slate-900 p-4" x-data="{ tolak: false }">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div>
                            <p class="font-semibold text-slate-100 m-0">{{ $rujukan->siswa->nama ?? '-' }} <span class="text-slate-400 font-normal text-sm">&middot; {{ $rujukan->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $rujukan->siswa->nis ?? '-' }}</span></p>
                            <p class="text-xs text-slate-500 mt-0.5 m-0">Dirujuk oleh {{ $rujukan->dirujukOleh->nama ?? '-' }} &middot; {{ $rujukan->created_at->translatedFormat('d M Y, H:i') }}</p>
                        </div>
                        <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-xs font-bold text-blue-400">{{ $rujukan->kategori }}</span>
                    </div>
                    <p class="text-sm text-slate-300 mt-2 mb-3 whitespace-pre-line">{{ $rujukan->alasan }}</p>

                    <div x-show="!tolak" class="flex gap-2">
                        <form method="POST" action="{{ route('guru.bk.rujukan.accept', $rujukan) }}" onsubmit="return confirm('Terima rujukan ini? Kasus BK baru akan dibuat dengan Anda sebagai konselor.')">
                            @csrf
                            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500"><i class="fa-solid fa-check mr-1"></i> Terima</button>
                        </form>
                        <button type="button" @click="tolak = true" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-xmark mr-1"></i> Tolak</button>
                    </div>
                    <form x-show="tolak" x-cloak method="POST" action="{{ route('guru.bk.rujukan.reject', $rujukan) }}" class="flex gap-2">
                        @csrf
                        <input type="text" name="catatan_penolakan" placeholder="Alasan penolakan (opsional)..." class="flex-1 rounded-lg border border-slate-600 bg-slate-800 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                        <button type="submit" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Kirim Penolakan</button>
                        <button type="button" @click="tolak = false" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                    </form>
                </div>
            @empty
                <p class="text-slate-400 text-sm m-0">Tidak ada rujukan yang menunggu.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
        <h6 class="font-bold text-slate-100 mb-3">Riwayat Ditangani</h6>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-sm">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                    <tr>
                        <th class="px-3 py-2">Siswa</th>
                        <th class="px-3 py-2">Dirujuk Oleh</th>
                        <th class="px-3 py-2">Status</th>
                        <th class="px-3 py-2">Ditangani Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($riwayat as $rujukan)
                        <tr>
                            <td class="px-3 py-2 text-slate-100">{{ $rujukan->siswa->nama ?? '-' }}</td>
                            <td class="px-3 py-2 text-slate-400">{{ $rujukan->dirujukOleh->nama ?? '-' }}</td>
                            <td class="px-3 py-2">
                                @if($rujukan->status === 'diterima')
                                    <a href="{{ route('guru.bk.kasus.show', $rujukan->kasus_bk_id) }}" class="rounded-full bg-emerald-500/10 px-2.5 py-1 text-xs font-bold text-emerald-400 hover:bg-emerald-500/20">Diterima &rarr;</a>
                                @else
                                    <span class="rounded-full bg-rose-500/10 px-2.5 py-1 text-xs font-bold text-rose-400" title="{{ $rujukan->catatan_penolakan }}">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-slate-400">{{ $rujukan->ditanganiOleh->nama ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-6 text-center text-slate-400">Belum ada rujukan yang ditangani.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
