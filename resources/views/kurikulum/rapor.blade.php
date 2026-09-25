<div id="pane-kurikulum-rapor" class="pane-content hidden-pane fade-transition">
    <!-- Announcement publishing board -->
    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
        <h5 class="font-bold text-slate-100 mb-2"><i
                class="fa-solid fa-bullhorn text-warning mr-1"></i> Siaran Pengumuman Kurikulum
        </h5>
        <p class="text-slate-400 small mb-3">Siarkan pengumuman terkait KBM / Akademik secara
            real-time.</p>
        <form onsubmit="event.preventDefault(); broadcastAnnouncementK();" class="grid grid-cols-1 md:grid-cols-12 gap-2">
            <div class="md:col-span-3">
                <input type="text" id="bc-title-k" class="form-control form-control-sm"
                    placeholder="Judul Pengumuman..." required>
            </div>
            <div class="md:col-span-7">
                <input type="text" id="bc-desc-k" class="form-control form-control-sm"
                    placeholder="Isi detail pengumuman..." required>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="btn btn-brand-primary btn-sm w-full font-bold"><i
                        class="fa-solid fa-paper-plane mr-1"></i> Siarkan</button>
            </div>
        </form>
    </div>

    <h4 class="font-bold text-slate-100 mb-2">Cetak e-Rapor & Legger</h4>
    <p class="text-slate-400 small mb-4">Pengelolaan pencetakan rapor semester siswa per Rombel.
    </p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-100" id="kurikulum-rapor-grid">
        @php
            $kelasData = \App\Models\Kelas::with('waliKelas')->get();
        @endphp
        @foreach($kelasData as $kelas)
            <div class="col-span-1">
                <div class="card p-3 border-0 rounded-xl shadow-sm bg-slate-800/80 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <h6 class="font-bold m-0 text-slate-100">{{ $kelas->nama_kelas }}</h6>
                            <span class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20 badge-pill-custom">Menunggu Validasi</span>
                        </div>
                        <p class="text-slate-400 small m-0 mb-3">Wali Kelas: {{ $kelas->waliKelas->nama ?? '-' }}</p>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="triggerToast('Legger {{ $kelas->nama_kelas }} dicetak')" class="btn btn-outline-secondary btn-sm rounded-lg flex-grow-1">Cetak Legger</button>
                        <button onclick="triggerToast('e-Rapor {{ $kelas->nama_kelas }} digenerate')" class="btn btn-brand-primary btn-sm rounded-lg flex-grow-1" disabled>Cetak Rapor</button>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
