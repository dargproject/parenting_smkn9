@php
    $jadwalPerHari = $jadwalGuruMapel->groupBy('hari')->map(fn ($g) => $g->map(fn ($j) => [
        'id' => $j->id,
        'label' => ($j->kelas->nama_kelas ?? '-').' — '.($j->mataPelajaran->nama_mapel ?? '-').' ('.substr($j->jam_mulai, 0, 5).'-'.substr($j->jam_selesai, 0, 5).')',
    ])->values());
@endphp
<div id="pane-guru-mapel-dashboard" class="pane-content hidden-pane fade-transition" x-data="jurnalForm(@js($jadwalPerHari))">

    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
        <h5 class="font-bold text-slate-100 mb-2"><i class="fa-regular fa-clock text-info mr-1"></i> Jadwal Mengajar Anda (Hari Ini &mdash; {{ $namaHariIni }})</h5>
        <p class="text-slate-400 small mb-3">Status pengisian jurnal untuk jadwal hari ini.</p>
        <div class="overflow-x-auto">
            <table class="table table-hover table-striped align-middle table-sm" style="font-size: 13px;">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                    <tr>
                        <th>Jam Ke / Waktu</th>
                        <th>Kelas Rombel</th>
                        <th>Mata Pelajaran</th>
                        <th>Ruang / Lab</th>
                        <th class="text-center">Status Jurnal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalHariIni as $jadwal)
                        <tr>
                            <td class="font-bold">{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                            <td>{{ $jadwal->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $jadwal->ruang }}</span></td>
                            <td class="text-center">
                                @if($jurnalHariIniIds->contains($jadwal->id))
                                    <x-status-badge tone="emerald">Jurnal Terisi</x-status-badge>
                                @else
                                    <x-status-badge tone="amber">Belum di-Jurnal</x-status-badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 small py-3">Tidak ada jadwal mengajar hari ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100 border-t border-slate-700/60 border-blue-500 border-4">
        <h5 class="font-bold text-slate-100 mb-2"><i class="fa-solid fa-calendar-plus text-primary mr-1"></i> Formulir Jurnal Mengajar & Presensi</h5>
        <p class="text-slate-400 small mb-3">Pilih tanggal lalu jadwal mengajar Anda pada tanggal tersebut. Jika jurnal untuk kombinasi ini sudah pernah diisi, datanya akan dimuat kembali untuk diperbarui.</p>

        <form method="POST" action="{{ route('guru.jurnal.store') }}" enctype="multipart/form-data" class="row g-2 mb-3 bg-slate-800/50 p-3 rounded border">
            @csrf
            <div class="col-md-4">
                <label class="form-label small font-semibold">Tanggal</label>
                <input type="date" name="tanggal" x-model="tanggal" @change="onTanggalBerubah()" max="{{ now()->toDateString() }}" required class="form-control form-control-sm text-slate-100">
            </div>
            <div class="col-md-8">
                <label class="form-label small font-semibold">Pilih Jadwal Mengajar</label>
                <select name="jadwal_pelajaran_id" x-model="jadwalId" @change="muatPresensi()" required class="form-select form-select-sm text-slate-100">
                    <option value="">-- Pilih tanggal terlebih dahulu --</option>
                    <template x-for="j in opsiJadwal" :key="j.id">
                        <option :value="j.id" x-text="j.label"></option>
                    </template>
                </select>
                <p class="text-xs text-amber-400 mt-1" x-show="tanggal && opsiJadwal.length === 0" style="display: none;">Tidak ada jadwal mengajar Anda pada hari itu.</p>
            </div>
            <div class="col-md-8 mt-2">
                <label class="form-label small font-semibold">Materi Pembelajaran / Kompetensi Dasar</label>
                <input type="text" name="materi" x-model="materi" class="form-control form-control-sm" placeholder="Contoh: Konfigurasi routing static, CRUD PHP..." required>
            </div>
            <div class="col-md-4 mt-2">
                <label class="form-label small font-semibold"><i class="fa-solid fa-camera"></i> Dokumentasi Foto Kelas (opsional)</label>
                <input type="file" name="foto" accept="image/*" class="form-control form-control-sm text-slate-100">
            </div>

            <template x-if="memuat">
                <p class="text-slate-400 text-sm mt-3"><i class="fa-solid fa-spinner fa-spin mr-1"></i> Memuat daftar siswa...</p>
            </template>

            <div class="overflow-x-auto mt-3" x-show="siswaList.length > 0" style="display: none;">
                <h6 class="font-bold small text-slate-100 mb-2"><i class="fa-solid fa-users mr-1 text-primary"></i> Daftar Presensi Rombel</h6>
                <table class="w-full text-left text-sm text-slate-400 table-sm" style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th>Nama Siswa</th>
                            <th class="text-center" style="width: 260px;">Kehadiran</th>
                            <th>Keterangan / Alasan Khusus</th>
                        </tr>
                    </thead>
                    <tbody id="jurnal-presensi-tbody">
                        <template x-for="s in siswaList" :key="s.id">
                            <tr class="border-b border-slate-700/60">
                                <td class="font-bold py-2 text-slate-100" x-text="s.nama + ' (NIS ' + s.nis + ')'"></td>
                                <td class="text-center py-2">
                                    <div class="flex justify-center gap-3">
                                        <template x-for="opt in [
                                            { kode: 'H', warna: 'text-emerald-400', accent: 'accent-emerald-500' },
                                            { kode: 'S', warna: 'text-cyan-400', accent: 'accent-cyan-500' },
                                            { kode: 'I', warna: 'text-amber-400', accent: 'accent-amber-500' },
                                            { kode: 'A', warna: 'text-rose-400', accent: 'accent-rose-500' },
                                        ]" :key="opt.kode">
                                            <label class="flex items-center gap-1 cursor-pointer font-medium" :class="opt.warna">
                                                <input type="radio" :name="'kehadiran[' + s.id + '][status]'" :value="opt.kode" x-model="kehadiran[s.id].status" :class="opt.accent">
                                                <span x-text="opt.kode"></span>
                                            </label>
                                        </template>
                                    </div>
                                </td>
                                <td class="py-2">
                                    <input type="text" :name="'kehadiran[' + s.id + '][keterangan]'" x-model="kehadiran[s.id].keterangan" class="form-control form-control-sm text-slate-100 bg-slate-900 border-slate-700/60" placeholder="Keterangan (opsional)">
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div class="text-end mt-3">
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-lg transition-colors">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Simpan Jurnal & Presensi
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="card border-0 rounded-xl shadow-sm p-4 bg-slate-800/80 mb-4 text-slate-100">
        <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-book mr-1 text-info"></i> Riwayat Jurnal Mengajar Anda</h6>
        <div class="overflow-x-auto">
            <table class="table table-hover table-striped align-middle table-sm" style="font-size: 13px;">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                    <tr>
                        <th>Tanggal</th>
                        <th>Materi Pembelajaran</th>
                        <th>Kelas</th>
                        <th>Jam</th>
                        <th>Foto Mengajar</th>
                    </tr>
                </thead>
                <tbody>
                    @php $myJournals = \App\Models\JurnalMengajar::with(['jadwalPelajaran.kelas'])->where('guru_id', $guru->id)->latest('tanggal')->take(20)->get(); @endphp
                    @forelse($myJournals as $jurnal)
                        <tr>
                            <td class="font-monospace small">{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d-m-Y') }}</td>
                            <td class="font-bold">{{ $jurnal->materi }}</td>
                            <td>{{ $jurnal->jadwalPelajaran->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $jurnal->jadwalPelajaran->jam_mulai ?? '-' }}</td>
                            <td>
                                @if($jurnal->foto)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($jurnal->foto) }}" target="_blank" class="badge bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"><i class="fa-solid fa-image"></i> Lihat foto</a>
                                @else
                                    <span class="badge bg-secondary-subtle text-slate-400 py-1 px-2.5">Tidak ada foto</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-slate-400 small py-3">Belum ada riwayat pengisian jurnal mengajar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Dipanggil dari menu Riwayat Jurnal (kalender) agar tombol "Lengkapi Jurnal" langsung
    // membuka form ini dengan tanggal terisi, tanpa perlu mengetik ulang.
    function bukaJurnalTanggal(tanggal, jadwalId) {
        const link = document.querySelector('#sidebar-menu-list a[onclick*="pane-guru-mapel-dashboard"]');
        showPane('pane-guru-mapel-dashboard', link || null);

        const el = document.getElementById('pane-guru-mapel-dashboard');
        const data = Alpine.$data(el);
        data.tanggal = tanggal;
        data.onTanggalBerubah();
        if (jadwalId) {
            data.jadwalId = String(jadwalId);
            data.muatPresensi();
        }

        setTimeout(() => el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
    }

    function jurnalForm(jadwalPerHari) {
        const HARI = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return {
            tanggal: '{{ now()->toDateString() }}',
            jadwalId: '',
            materi: '',
            opsiJadwal: [],
            siswaList: [],
            kehadiran: {},
            memuat: false,
            init() { this.onTanggalBerubah(); },
            onTanggalBerubah() {
                if (!this.tanggal) { this.opsiJadwal = []; return; }
                const hari = HARI[new Date(this.tanggal + 'T00:00:00').getDay()];
                this.opsiJadwal = jadwalPerHari[hari] || [];
                this.jadwalId = '';
                this.siswaList = [];
                this.materi = '';
            },
            async muatPresensi() {
                this.siswaList = [];
                this.kehadiran = {};
                if (!this.jadwalId) return;
                this.memuat = true;
                try {
                    const res = await fetch(`/guru/api/siswa-by-jadwal/${this.jadwalId}?tanggal=${this.tanggal}`);
                    const data = await res.json();
                    this.materi = data.materi || '';
                    const presensi = data.presensi || {};
                    this.siswaList = data.siswa || [];
                    this.siswaList.forEach(s => {
                        this.kehadiran[s.id] = presensi[s.id] ? { status: presensi[s.id].status, keterangan: presensi[s.id].keterangan || '' } : { status: 'H', keterangan: '' };
                    });
                } finally {
                    this.memuat = false;
                }
            },
        };
    }
</script>
