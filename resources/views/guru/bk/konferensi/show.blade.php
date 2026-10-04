@extends('layouts.app')

@section('content')
@php $kasusBk = $konferensi->kasusBk; @endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ route('guru.bk.konferensi.index') }}" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali ke Daftar Konferensi Kasus</a>
            <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">Konferensi &middot; {{ $kasusBk->siswa->nama ?? '-' }}</h4>
            <p class="text-slate-400 text-sm m-0">{{ $kasusBk->judul }} &middot; {{ $konferensi->tanggal_konferensi->translatedFormat('d M Y') }}{{ $konferensi->tempat_pertemuan ? ' · '.$konferensi->tempat_pertemuan : '' }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
        </div>
        <div class="flex gap-2">
            <button type="button" onclick="document.getElementById('modal-panggilan-ortu-konferensi').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-phone mr-1"></i> Buat Panggilan Ortu</button>
            <button type="button" onclick="document.getElementById('modal-edit-konferensi').style.display='flex'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300 hover:border-blue-500"><i class="fa-solid fa-pen mr-1"></i> Edit</button>
            <form method="POST" action="{{ route('guru.bk.konferensi.destroy', $konferensi) }}" onsubmit="return confirm('Hapus konferensi kasus ini beserta seluruh lampirannya?')">
                @csrf @method('DELETE')
                <button type="submit" class="rounded-lg border border-rose-500/40 px-4 py-2 text-sm font-semibold text-rose-400 hover:bg-rose-500/10"><i class="fa-solid fa-trash mr-1"></i> Hapus</button>
            </form>
        </div>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-5">
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
                <h6 class="font-bold text-slate-100 mb-3">Peserta Konferensi ({{ $konferensi->pesertas->count() }})</h6>
                <div class="flex flex-col gap-2">
                    @foreach($konferensi->pesertas as $peserta)
                        <div class="flex items-center justify-between rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm">
                            <span class="text-slate-100 font-semibold">{{ $peserta->nama_peserta }}</span>
                            <span class="rounded-full bg-blue-500/10 px-2.5 py-0.5 text-xs font-bold text-blue-400">{{ $peserta->peran_peserta }}</span>
                        </div>
                    @endforeach
                </div>
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
                <h6 class="font-bold text-slate-100 mb-3">Kasus BK Terkait</h6>
                <p class="text-slate-400 text-sm mb-2">Status: <span class="font-semibold text-slate-100">{{ ucfirst($kasusBk->status) }}</span></p>
                <a href="{{ route('guru.bk.kasus.show', $kasusBk) }}" class="text-sm text-blue-400 hover:text-blue-300">Lihat detail kasus <i class="fa-solid fa-arrow-right ml-1"></i></a>
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

@include('guru.bk.konferensi._form-modal', ['formId' => 'modal-edit-konferensi', 'konferensi' => $konferensi])
@include('guru.bk.partials._panggilan-ortu-modal', ['formId' => 'modal-panggilan-ortu-konferensi', 'siswaId' => $kasusBk->siswa_id, 'alasanDefault' => 'Undangan konferensi kasus: '.$kasusBk->judul.' pada '.$konferensi->tanggal_konferensi->translatedFormat('d M Y')])
@endsection
