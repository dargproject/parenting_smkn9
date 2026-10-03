@extends('layouts.app')

@section('content')
@php
    $profil = $pengunduranDiri->siswa->profilSiswa ?? null;
    $keluarga = $pengunduranDiri->siswa->dataKeluarga ?? null;
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.pengunduran-diri.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Pengunduran Diri</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">Pengunduran Diri &middot; {{ $pengunduranDiri->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $pengunduranDiri->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $pengunduranDiri->siswa->nis ?? '-' }} &middot; {{ $pengunduranDiri->tanggal_pengunduran->translatedFormat('d M Y') }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-edit-pengunduran').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.pengunduran-diri.destroy', $pengunduranDiri) }}" onsubmit="return confirm('Hapus catatan pengunduran diri ini?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Data Pengunduran Diri</h6>
                <div>
                    <dt class="text-slate-400 text-sm">Nama Orang Tua / Wali</dt>
                    <dd class="text-slate-200 mt-1">{{ $pengunduranDiri->nama_ortu_wali }}</dd>
                </div>
                <div class="mt-4">
                    <dt class="text-slate-400 text-sm">Alamat Orang Tua / Wali</dt>
                    <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $pengunduranDiri->alamat_ortu_wali }}</dd>
                </div>
                <div class="mt-4">
                    <dt class="text-slate-400 text-sm">Alasan Pengunduran Diri</dt>
                    <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $pengunduranDiri->alasan_pengunduran }}</dd>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Lampiran</h6>
                @forelse($pengunduranDiri->lampirans as $lampiran)
                    <a href="{{ \Illuminate\Support\Facades\Storage::url($lampiran->path_file) }}" target="_blank" class="flex items-center justify-between gap-2 rounded-lg border border-slate-700/60 px-3 py-2 text-sm text-blue-400 hover:bg-slate-900 mb-2">
                        <span class="truncate"><i class="fa-solid fa-paperclip mr-1.5"></i>{{ $lampiran->nama_file }}</span>
                        <span class="text-slate-500 text-xs whitespace-nowrap">{{ $lampiran->ukuran ? round($lampiran->ukuran / 1024, 1).' KB' : '' }}</span>
                    </a>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada lampiran.</p>
                @endforelse
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-house mr-1.5"></i>Data Keluarga (Referensi)</h6>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-400">Alamat Rumah (Profil Siswa)</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $profil->alamat ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Nama Ayah</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $keluarga->nama_ayah ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Nama Ibu</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $keluarga->nama_ibu ?? '-' }}</dd>
                    </div>
                </dl>
                <p class="mt-3 text-xs text-slate-500 m-0">Data pembanding dari Profil Siswa & Data Keluarga -- nama/alamat yang dicatat di atas bisa berbeda bila diisi langsung oleh konselor.</p>
            </div>
        </div>
    </div>
</div>

@include('guru.bk.pengunduran-diri._form-modal', ['formId' => 'modal-edit-pengunduran', 'siswas' => collect([$pengunduranDiri->siswa]), 'pengunduranDiri' => $pengunduranDiri])
@endsection
