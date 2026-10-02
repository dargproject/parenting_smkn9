@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.kasus.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Kasus</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">{{ $kasusBk->judul }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $kasusBk->siswa->nama ?? '-' }} &middot; {{ $kasusBk->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $kasusBk->siswa->nis ?? '-' }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-kasus').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.kasus.destroy', $kasusBk) }}" onsubmit="return confirm('Hapus kasus ini beserta seluruh lampirannya?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Data Kasus</h6>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-slate-400">Kategori</dt><dd class="text-slate-100 font-semibold">{{ $kasusBk->kategoriKasus->nama_kategori ?? $kasusBk->kategori }}</dd></div>
                    <div><dt class="text-slate-400">Tanggal Mulai</dt><dd class="text-slate-100 font-semibold">{{ optional($kasusBk->tanggal_mulai)->translatedFormat('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-slate-400">Tanggal Selesai</dt><dd class="text-slate-100 font-semibold">{{ optional($kasusBk->tanggal_selesai)->translatedFormat('d M Y') ?? '-' }}</dd></div>
                    <div><dt class="text-slate-400">Dicatat Oleh</dt><dd class="text-slate-100 font-semibold">{{ $kasusBk->konselor->nama ?? '-' }}</dd></div>
                </dl>
                <div class="mt-4">
                    <dt class="text-slate-400 text-sm">Deskripsi / Uraian Masalah</dt>
                    <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $kasusBk->deskripsi }}</dd>
                </div>
                @if($kasusBk->tindak_lanjut)
                    <div class="mt-4">
                        <dt class="text-slate-400 text-sm">Tindak Lanjut</dt>
                        <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $kasusBk->tindak_lanjut }}</dd>
                    </div>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Lampiran</h6>
                @forelse($kasusBk->lampiran as $lampiran)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($lampiran->path_file) }}" target="_blank" class="flex items-center justify-between gap-2 rounded-lg border border-slate-700/60 px-3 py-2 text-sm text-blue-400 hover:bg-slate-900 mb-2">
                        <span class="truncate"><i class="fa-solid fa-paperclip mr-1.5"></i>{{ $lampiran->nama_file }}</span>
                        <span class="text-slate-500 text-xs whitespace-nowrap">{{ $lampiran->ukuran ? round($lampiran->ukuran / 1024, 1).' KB' : '' }}</span>
                    </a>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada lampiran.</p>
                @endforelse
            </div>

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Export Dokumen Word</h6>
                <div class="flex flex-wrap gap-2">
                    @foreach(['form-penanganan-siswa' => 'Kartu Penanganan Siswa', 'komulatif-record' => 'Comulative Record', 'lembar-sosiometri' => 'Lembar Sosiometri'] as $template => $label)
                        <a href="{{ route('guru.bk.kasus.export', [$kasusBk, $template]) }}" class="rounded-lg border border-slate-600 px-3 py-2 text-sm text-slate-300 hover:border-blue-500 hover:text-slate-100"><i class="fa-solid fa-file-word mr-1.5 text-blue-400"></i>{{ $label }}</a>
                    @endforeach
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Layanan Terkait</h6>
                <p class="text-slate-400 text-sm m-0">Konseling individu/kelompok, kunjungan rumah, alih tangan, dan konferensi kasus yang ditempel ke kasus ini akan tampil di sini (menyusul di fase berikutnya).</p>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Status Kasus</h6>
                <form method="POST" action="{{ route('guru.bk.kasus.status', $kasusBk) }}">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        @foreach(['antrean' => 'Antrean', 'proses' => 'Sedang Diproses', 'selesai' => 'Selesai (Ditutup)'] as $value => $label)
                            <option value="{{ $value }}" @selected($kasusBk->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </div>
</div>

@include('guru.bk.kasus._form-modal', ['formId' => 'modal-edit-kasus', 'siswas' => $siswas, 'kategoriKasusList' => $kategoriKasusList, 'kasusBk' => $kasusBk])
@endsection
