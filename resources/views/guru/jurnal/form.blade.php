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
                <div class="flex gap-2">
                    <button type="button" @click="$refs.fotoGaleri.click()" class="btn btn-sm btn-outline-secondary text-slate-100 flex-1">
                        <i class="fa-solid fa-image"></i> Pilih File
                    </button>
                    <button type="button" @click="bukaKamera()" class="btn btn-sm btn-outline-secondary text-slate-100 flex-1">
                        <i class="fa-solid fa-camera"></i> Kamera
                    </button>
                </div>
                <input type="file" name="foto" x-ref="fotoFinal" accept="image/*" class="hidden">
                <input type="file" x-ref="fotoGaleri" accept="image/*" class="hidden" @change="kompresFoto($event, $refs.fotoFinal)">
                <p class="text-xs text-slate-400 mt-1" x-show="fotoStatus" x-text="fotoStatus" style="display: none;"></p>
                @error('foto')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div
                x-show="kameraAktif"
                style="display: none;"
                class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/70"
                @keydown.escape.window="tutupKamera()"
            >
                <div class="w-full max-w-md rounded-2xl bg-slate-800 p-4 text-slate-100 shadow-xl">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="text-base font-semibold">Ambil Foto</h3>
                        <button type="button" @click="tutupKamera()" class="text-slate-400 hover:text-slate-200">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <p class="text-xs text-rose-400 mb-2" x-show="kameraError" x-text="kameraError" style="display: none;"></p>

                    <video x-ref="videoKamera" autoplay playsinline muted class="w-full rounded-lg bg-black" x-show="!kameraError" style="display: none;"></video>

                    <div class="mt-3 flex gap-2">
                        <button type="button" @click="ambilFotoDariKamera()" x-show="!kameraError" style="display: none;" class="btn btn-sm btn-primary flex-1">
                            <i class="fa-solid fa-camera"></i> Jepret
                        </button>
                        <button type="button" @click="tutupKamera()" class="btn btn-sm btn-outline-secondary text-slate-100 flex-1">
                            Batal
                        </button>
                    </div>
                </div>
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
                        <th class="text-center">Aksi</th>
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
                            <td class="text-center whitespace-nowrap">
                                <button type="button" onclick="bukaJurnalTanggal('{{ \Carbon\Carbon::parse($jurnal->tanggal)->toDateString() }}', {{ $jurnal->jadwal_pelajaran_id }})" class="btn btn-outline-primary btn-xs py-0.5 px-2 me-1" style="font-size: 11px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                <form method="POST" action="{{ route('guru.jurnal.destroy', $jurnal) }}" onsubmit="return confirm('Hapus jurnal tanggal {{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d-m-Y') }} ini beserta presensi siswanya? Tindakan ini tidak bisa dibatalkan.')" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs py-0.5 px-2" style="font-size: 11px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 small py-3">Belum ada riwayat pengisian jurnal mengajar.</td></tr>
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
            fotoStatus: '',
            kameraAktif: false,
            kameraError: '',
            kameraStream: null,
            init() { this.onTanggalBerubah(); },
            async kompresFoto(event, targetInput) {
                const file = event.target.files[0];
                if (!file || !file.type.startsWith('image/')) { this.fotoStatus = ''; return; }

                const ukuranAsliMb = (file.size / 1024 / 1024).toFixed(1);
                this.fotoStatus = 'Memproses foto...';
                let fileUntukDikirim = file;

                try {
                    const bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
                    const MAX_SISI = 1600;
                    let { width, height } = bitmap;
                    if (width > MAX_SISI || height > MAX_SISI) {
                        const skala = MAX_SISI / Math.max(width, height);
                        width = Math.round(width * skala);
                        height = Math.round(height * skala);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    canvas.getContext('2d').drawImage(bitmap, 0, 0, width, height);
                    bitmap.close();

                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.75));
                    if (!blob) throw new Error('Gagal memproses foto');

                    const namaFile = file.name.replace(/\.[^.]+$/, '') + '.jpg';
                    fileUntukDikirim = new File([blob], namaFile, { type: 'image/jpeg' });

                    this.fotoStatus = `Foto dikompres: ${ukuranAsliMb}MB → ${(blob.size / 1024).toFixed(0)}KB`;
                } catch (e) {
                    // Kompresi gagal (browser lama / format tak didukung) -- biarkan file asli terkirim,
                    // server tetap punya pengaman resize sendiri di sisi backend.
                    this.fotoStatus = `Foto dipilih (tanpa kompresi otomatis): ${file.name}`;
                }

                const dt = new DataTransfer();
                dt.items.add(fileUntukDikirim);
                targetInput.files = dt.files;
            },
            async bukaKamera() {
                this.kameraError = '';
                this.kameraAktif = true;

                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    this.kameraError = 'Browser ini tidak mendukung akses kamera langsung. Gunakan tombol "Pilih File" sebagai gantinya.';
                    return;
                }

                try {
                    this.kameraStream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: { ideal: 'environment' } },
                        audio: false,
                    });
                    this.$refs.videoKamera.srcObject = this.kameraStream;
                } catch (e) {
                    this.kameraError = e.name === 'NotAllowedError'
                        ? 'Izin kamera ditolak. Aktifkan izin kamera untuk situs ini di pengaturan browser, atau gunakan tombol "Pilih File".'
                        : 'Kamera tidak dapat diakses. Gunakan tombol "Pilih File" sebagai gantinya.';
                }
            },
            tutupKamera() {
                if (this.kameraStream) {
                    this.kameraStream.getTracks().forEach(track => track.stop());
                    this.kameraStream = null;
                }
                this.kameraAktif = false;
            },
            async ambilFotoDariKamera() {
                const video = this.$refs.videoKamera;
                const MAX_SISI = 1600;
                let width = video.videoWidth;
                let height = video.videoHeight;
                if (width > MAX_SISI || height > MAX_SISI) {
                    const skala = MAX_SISI / Math.max(width, height);
                    width = Math.round(width * skala);
                    height = Math.round(height * skala);
                }

                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(video, 0, 0, width, height);

                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.75));
                if (!blob) { this.kameraError = 'Gagal mengambil foto, coba lagi.'; return; }

                const fileBaru = new File([blob], `kamera-${Date.now()}.jpg`, { type: 'image/jpeg' });
                const dt = new DataTransfer();
                dt.items.add(fileBaru);
                this.$refs.fotoFinal.files = dt.files;

                this.fotoStatus = `Foto dari kamera: ${(blob.size / 1024).toFixed(0)}KB`;
                this.tutupKamera();
            },
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
