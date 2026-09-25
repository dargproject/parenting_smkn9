<div id="pane-guru-wali-catatan" class="pane-content hidden-pane fade-transition space-y-4">
    <div>
        <h4 class="font-bold text-slate-100 mb-1 text-xl">Catatan Wali Kelas</h4>
        <p class="text-slate-400 text-sm">Catatan perkembangan karakter, rekap ketidakhadiran, dan kegiatan ekstrakurikuler siswa binaan.</p>
    </div>

    @forelse($siswaBinaan as $siswa)
        @php $cw = $catatanWaliKelasBinaan[$siswa->id] ?? null; @endphp
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6">
            <h6 class="font-bold text-slate-100 mb-3">{{ $siswa->nama }} <span class="text-slate-400 font-normal text-sm">&middot; {{ $siswa->kelas->nama_kelas ?? '-' }}</span></h6>
            <form method="POST" action="{{ route('guru.wali.catatan.store') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="siswa_id" value="{{ $siswa->id }}">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Catatan Karakter</label>
                    <textarea name="catatan_karakter" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ $cw->catatan_karakter ?? '' }}</textarea>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-400">Sakit</label>
                        <input type="number" min="0" name="sakit" value="{{ $cw->sakit ?? 0 }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-400">Izin</label>
                        <input type="number" min="0" name="izin" value="{{ $cw->izin ?? 0 }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-400">Tanpa Keterangan</label>
                        <input type="number" min="0" name="tanpa_keterangan" value="{{ $cw->tanpa_keterangan ?? 0 }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Kegiatan Ekstrakurikuler</label>
                    <textarea name="catatan_ekskul" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ $cw->catatan_ekskul ?? '' }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan Catatan</button>
                </div>
            </form>
        </div>
    @empty
        <p class="text-slate-400 text-sm">Anda belum menjadi guru wali (akademik) untuk kelas manapun.</p>
    @endforelse
</div>
