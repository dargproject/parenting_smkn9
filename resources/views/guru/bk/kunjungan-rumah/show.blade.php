@extends('layouts.app')

@section('content')
@php
    $kasusBk = $kunjunganRumah->kasusBk;
    $profil = $kasusBk->siswa->profilSiswa ?? null;
    $keluarga = $kasusBk->siswa->dataKeluarga ?? null;
    $tone = match($kunjunganRumah->status) {
        'diproses' => 'amber',
        'ditunda' => 'blue',
        'dibatalkan' => 'rose',
        default => 'slate',
    };
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.kunjungan-rumah.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Kunjungan Rumah</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">{{ $kasusBk->judul }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $kasusBk->siswa->nama ?? '-' }} &middot; {{ $kasusBk->siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $kasusBk->siswa->nis ?? '-' }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-panggilan-ortu-kunjungan').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-phone mr-1"></i> Buat Panggilan Ortu</button>
            <button type="button" onclick="document.getElementById('modal-edit-kunjungan').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.kunjungan-rumah.destroy', $kunjunganRumah) }}" onsubmit="return confirm('Hapus kunjungan rumah ini beserta seluruh lampirannya?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h6 class="font-bold text-slate-100 m-0">Data Kunjungan</h6>
                    <x-status-badge :tone="$tone">{{ ucfirst($kunjunganRumah->status) }}</x-status-badge>
                </div>
                <div>
                    <dt class="text-slate-400 text-sm">Penanganan</dt>
                    <dd class="text-slate-200 mt-1 whitespace-pre-line">{{ $kasusBk->judul }}</dd>
                </div>
                <div class="mt-4">
                    <dt class="text-slate-400 text-sm">Uraian Masalah</dt>
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
        </div>

        <div class="space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-house mr-1.5"></i>Data Keluarga & Alamat</h6>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-400">Alamat Rumah</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $profil->alamat ?? 'Belum diisi di Profil Siswa.' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Nama Ayah</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $keluarga->nama_ayah ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">Nama Ibu</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $keluarga->nama_ibu ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-400">No. HP Orang Tua</dt>
                        <dd class="text-slate-100 mt-0.5">{{ $keluarga->telp_ortu ?? $kasusBk->siswa->no_hp_ortu ?? '-' }}</dd>
                    </div>
                </dl>
                @if(! $profil && ! $keluarga)
                    <p class="mt-3 text-xs text-slate-500 m-0">Lengkapi Profil Siswa & Data Keluarga dari halaman Jejak Rekam Siswa agar info ini terisi.</p>
                @endif
            </div>

            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Panggilan Orang Tua</h6>
                @forelse($panggilanOrtus as $panggilan)
                    <div class="rounded-xl border border-slate-700/60 bg-slate-900 px-4 py-3 text-sm mb-2.5 last:mb-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-slate-100 font-semibold m-0"><i class="fa-regular fa-clock mr-1.5 text-blue-400"></i>{{ \Carbon\Carbon::parse($panggilan->tanggal)->translatedFormat('d M Y') }} &middot; {{ \Carbon\Carbon::parse($panggilan->waktu)->format('H:i') }} WIB</p>
                                @if($panggilan->ruang)
                                    <p class="text-slate-500 text-xs m-0 mt-0.5">{{ $panggilan->ruang }}</p>
                                @endif
                            </div>
                            <x-status-badge :tone="str_contains($panggilan->status, 'Hadir') ? 'emerald' : 'amber'">{{ $panggilan->status }}</x-status-badge>
                        </div>
                        <p class="text-slate-400 mt-2 mb-0">{{ $panggilan->alasan }}</p>
                        <div class="mt-3 flex items-center gap-3 border-t border-slate-700/60 pt-2.5">
                            <form method="POST" action="{{ route('panggilan-ortu.update-status', $panggilan) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="Hadir / Mediasi Selesai">
                                <button type="submit" class="text-xs font-semibold text-emerald-400 hover:text-emerald-300 disabled:opacity-40" {{ str_contains($panggilan->status, 'Hadir') ? 'disabled' : '' }}><i class="fa-solid fa-check mr-1"></i>Konfirmasi Hadir</button>
                            </form>
                            @if($panggilan->pemanggil_id === Auth::id())
                                <button type="button" onclick="document.getElementById('modal-edit-panggilan-{{ $panggilan->id }}').style.display='flex'" class="text-xs font-semibold text-blue-400 hover:text-blue-300"><i class="fa-solid fa-pen mr-1"></i>Edit</button>
                                <form method="POST" action="{{ route('panggilan-ortu.destroy', $panggilan) }}" onsubmit="return confirm('Hapus jadwal panggilan orang tua ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-400 hover:text-rose-300"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
                                </form>
                                @include('kesiswaan.partials._panggilan-ortu-edit-modal', ['panggilan' => $panggilan, 'formId' => 'modal-edit-panggilan-'.$panggilan->id])
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada panggilan orang tua untuk siswa ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@include('guru.bk.kunjungan-rumah._form-modal', ['formId' => 'modal-edit-kunjungan', 'siswas' => $siswas, 'kunjunganRumah' => $kunjunganRumah])
@include('guru.bk.partials._panggilan-ortu-modal', ['formId' => 'modal-panggilan-ortu-kunjungan', 'siswaId' => $kasusBk->siswa_id, 'alasanDefault' => 'Terkait kunjungan rumah: '.$kasusBk->judul])
@endsection
