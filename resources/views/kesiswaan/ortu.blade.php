<div id="pane-kesiswaan-ortu" class="pane-content hidden-pane fade-transition">
    <!-- Announcement publishing board -->
    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
        <h5 class="font-bold text-slate-100 mb-2"><i
                class="fa-solid fa-bullhorn text-warning mr-1"></i> Siaran Pengumuman Kesiswaan
        </h5>
        <p class="text-slate-400 small mb-3">Siarkan pengumuman terkait kedisiplinan dan tata
            tertib.</p>
        <form onsubmit="event.preventDefault(); broadcastAnnouncementS();" class="grid grid-cols-1 md:grid-cols-12 gap-2">
            <div class="md:col-span-3">
                <input type="text" id="bc-title-s" class="form-control form-control-sm"
                    placeholder="Judul Pengumuman..." required>
            </div>
            <div class="md:col-span-7">
                <input type="text" id="bc-desc-s" class="form-control form-control-sm"
                    placeholder="Isi detail pengumuman..." required>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn btn-brand-primary btn-sm w-full font-bold"><i
                        class="fa-solid fa-paper-plane mr-1"></i> Siarkan</button>
            </div>
        </form>
    </div>

    <div class="mb-4">
        <h4 class="font-bold m-0 text-slate-100">Mediasi & Panggilan Orang Tua</h4>
        <p class="text-slate-400 small m-0">Agenda mediasi kasus kedisiplinan berat bersama konselor BK.</p>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 mb-4 text-slate-100">
        <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-plus mr-1 text-blue-400"></i> Jadwalkan Panggilan Ortu</h6>
        <form method="POST" action="{{ route('guru.panggilan-ortu.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @csrf
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Siswa</label>
                @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas])
                @error('siswa_id')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Tanggal</label>
                <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                @error('tanggal')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Waktu</label>
                <input type="time" name="waktu" value="{{ old('waktu', '09:00') }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                @error('waktu')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Ruang</label>
                <input type="text" name="ruang" value="{{ old('ruang', 'Ruang BK') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-300">Alasan</label>
                <textarea name="alasan" rows="2" required placeholder="Alasan pemanggilan orang tua..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ old('alasan') }}</textarea>
                @error('alasan')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2 flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Jadwalkan</button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 text-slate-100" id="kesiswaan-summons-grid">
        @foreach($panggilanOrtus as $panggilan)
            @php
                $tone = str_contains($panggilan->status, 'Hadir') ? 'emerald' : 'amber';
            @endphp
            <div class="col-span-1">
                <div class="card p-3 border-0 rounded-xl shadow-sm bg-slate-800/80 border-l border-slate-700/60 border-4 border-warning">
                    <div class="flex justify-between align-items-start mb-2">
                        <div>
                            <h6 class="font-bold text-slate-100 mb-1">Mediasi: Wali dari {{ $panggilan->siswa->nama ?? '-' }}</h6>
                            <span class="text-slate-400 small">{{ $panggilan->siswa->kelas->nama_kelas ?? '-' }}</span>
                        </div>
                        <x-status-badge :tone="$tone">{{ $panggilan->status }}</x-status-badge>
                    </div>
                    <p class="small text-slate-400 m-0 mb-3"><i class="fa-regular fa-clock mr-1"></i> {{ \Carbon\Carbon::parse($panggilan->tanggal)->translatedFormat('l, d F Y') }} Pukul {{ \Carbon\Carbon::parse($panggilan->waktu)->format('H:i') }} WIB</p>
                    <div class="flex gap-2 mb-2">
                        <button onclick="triggerToast('Menghubungi Ortu {{ $panggilan->siswa->nama ?? '-' }}...')" class="btn btn-outline-secondary btn-sm rounded-lg flex-grow-1"><i class="fa-brands fa-whatsapp"></i> Chat</button>
                        <form method="POST" action="{{ route('panggilan-ortu.update-status', $panggilan) }}" class="flex-grow-1">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="Hadir / Mediasi Selesai">
                            <button type="submit" class="btn btn-brand-primary btn-sm rounded-lg w-full" {{ str_contains($panggilan->status, 'Hadir') ? 'disabled' : '' }}>Konfirmasi Hadir</button>
                        </form>
                    </div>
                    @if($panggilan->pemanggil_id === Auth::id())
                        <div class="flex gap-2">
                            <button type="button" onclick="document.getElementById('modal-edit-panggilan-{{ $panggilan->id }}').style.display='flex'" class="btn btn-outline-secondary btn-sm rounded-lg flex-grow-1"><i class="fa-solid fa-pen"></i> Edit</button>
                            <form method="POST" action="{{ route('panggilan-ortu.destroy', $panggilan) }}" onsubmit="return confirm('Hapus jadwal panggilan orang tua ini?')" class="flex-grow-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-lg w-full text-rose-400"><i class="fa-solid fa-trash"></i> Hapus</button>
                            </form>
                        </div>
                        @include('kesiswaan.partials._panggilan-ortu-edit-modal', ['panggilan' => $panggilan, 'formId' => 'modal-edit-panggilan-'.$panggilan->id])
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
