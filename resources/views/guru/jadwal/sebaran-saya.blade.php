{{--
    Sebaran Jadwal Saya (guru_mapel) -- pelengkap menu "Struktur Kurikulum" milik Waka Kurikulum.
    Guru mapel hanya bisa isi jadwal untuk kombinasi kelas+mapel yang SUDAH ditetapkan ke dirinya
    (KelasMataPelajaran.guru_id = guru login). Panel "Jadwal Kelas Ini" menampilkan SEMUA mapel yang
    sudah terjadwal di kelas tsb (bukan cuma miliknya) supaya tidak asal pilih jam yang sudah terpakai
    mapel lain -- sistem baru mendeteksi bentrok untuk mapel yang sama atau guru yang sama, belum untuk
    dua mapel berbeda di kelas yang sama pada jam yang sama.
--}}
<div id="pane-guru-mapel-jadwal" class="pane-content hidden-pane fade-transition">
    @php
        $kelasSayaIds = $kelasMapelBinaan->pluck('kelas_id')->unique()->values();
        $kelasSayaList = $kelasList->whereIn('id', $kelasSayaIds)->sortBy('nama_kelas', SORT_NATURAL)->values();
        $kmSayaPerKelas = $kelasMapelBinaan->groupBy('kelas_id')->map(fn ($g) => $g->map(fn ($km) => ['id' => $km->mata_pelajaran_id, 'label' => $km->mataPelajaran->nama_mapel ?? '-'])->sortBy('label')->values());
        $jadwalKelasSaya = $jadwalPelajarans->whereIn('kelas_id', $kelasSayaIds)
            ->groupBy('kelas_id')
            ->map(fn ($g) => $g->sortBy(fn ($j) => $j->hari.$j->jam_mulai)->map(fn ($j) => [
                'hari' => $j->hari, 'jam_mulai' => substr($j->jam_mulai, 0, 5), 'jam_selesai' => substr($j->jam_selesai, 0, 5),
                'mapel' => $j->mataPelajaran->nama_mapel ?? '-', 'guru' => $j->guru->nama ?? '-', 'punya_saya' => $j->guru_id === $guru->id,
            ])->values());
        $jadwalSayaSorted = $jadwalGuruMapel->sortBy(fn ($j) => $j->hari.$j->jam_mulai)->values();
    @endphp

    <h4 class="font-bold text-slate-100 mb-2">Sebaran Jadwal Saya</h4>
    <p class="text-slate-400 small mb-4">Isi jam mengajar Anda sendiri untuk mapel yang sudah ditetapkan Waka Kurikulum ke Anda. Kalau mapel Anda di suatu kelas belum muncul di sini, minta Waka Kurikulum menetapkannya dulu lewat menu "Struktur Kurikulum".</p>

    @if($kelasSayaList->isEmpty())
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-400">Belum ada mapel yang ditetapkan untuk Anda di kelas manapun. Hubungi Waka Kurikulum.</div>
    @else
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-6 text-slate-100 mb-4">
            <h6 class="font-bold text-slate-100 mb-3"><span id="jadwal-saya-form-title">Tambah Slot Jadwal</span></h6>
            <form id="jadwal-saya-form" method="POST" action="{{ route('guru.jadwal-mengajar.store') }}" data-store-action="{{ route('guru.jadwal-mengajar.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @csrf
                <input type="hidden" name="_method" value="POST" id="jadwal-saya-form-method">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Kelas</label>
                    <select name="kelas_id" required onchange="isiMapelJadwalSaya(); tampilkanJadwalKelas();" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="">Pilih kelas...</option>
                        @foreach($kelasSayaList as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        <option value="">Pilih kelas dulu...</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Hari</label>
                    <select name="hari" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                            <option value="{{ $hari }}">{{ $hari }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Jam Mulai</label>
                    <input type="time" name="jam_mulai" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Jam Selesai</label>
                    <input type="time" name="jam_selesai" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-400">Ruang</label>
                    <input type="text" name="ruang" placeholder="mis. Lab RPS 1" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                </div>
                <div class="md:col-span-3 flex justify-end gap-2">
                    <button type="button" id="jadwal-saya-batal" onclick="resetJadwalSayaForm()" class="hidden rounded-lg border border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:border-blue-500">Batal</button>
                    <button type="submit" id="jadwal-saya-submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Tambah Slot</button>
                </div>
            </form>

            <div id="jadwal-kelas-referensi" class="hidden mt-4 pt-4 border-t border-slate-700/60">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-2">Jadwal yang sudah terisi di kelas ini (semua mapel)</p>
                <div id="jadwal-kelas-referensi-list" class="flex flex-col gap-1.5"></div>
            </div>
        </div>

        <script>
            const KELAS_MAPEL_SAYA = @json($kmSayaPerKelas);
            const JADWAL_KELAS_SAYA = @json($jadwalKelasSaya);

            function isiMapelJadwalSaya(pilihId) {
                const f = document.getElementById('jadwal-saya-form');
                const sel = f.elements['mata_pelajaran_id'];
                const kelasId = f.elements['kelas_id'].value;
                const daftar = KELAS_MAPEL_SAYA[kelasId] || [];
                sel.innerHTML = '';
                const ph = document.createElement('option');
                ph.value = '';
                ph.textContent = kelasId ? 'Pilih mata pelajaran...' : 'Pilih kelas dulu...';
                sel.appendChild(ph);
                daftar.forEach(m => { const o = document.createElement('option'); o.value = m.id; o.textContent = m.label; sel.appendChild(o); });
                if (pilihId) sel.value = String(pilihId);
            }

            function tampilkanJadwalKelas() {
                const kelasId = document.getElementById('jadwal-saya-form').elements['kelas_id'].value;
                const panel = document.getElementById('jadwal-kelas-referensi');
                const list = document.getElementById('jadwal-kelas-referensi-list');
                const daftar = JADWAL_KELAS_SAYA[kelasId] || [];
                if (!kelasId) { panel.classList.add('hidden'); return; }
                panel.classList.remove('hidden');
                list.innerHTML = daftar.length ? '' : '<p class="text-slate-400 text-sm m-0">Belum ada jadwal terisi di kelas ini.</p>';
                daftar.forEach(j => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center justify-between rounded-lg bg-slate-900/50 px-3 py-1.5 text-sm';
                    row.innerHTML = '<span class="text-slate-300">' + j.hari + ', ' + j.jam_mulai + '-' + j.jam_selesai + ' &middot; ' + j.mapel + '</span>'
                        + '<span class="text-xs ' + (j.punya_saya ? 'text-blue-400' : 'text-slate-500') + '">' + j.guru + (j.punya_saya ? ' (Anda)' : '') + '</span>';
                    list.appendChild(row);
                });
            }

            function editJadwalSaya(btn) {
                const f = document.getElementById('jadwal-saya-form');
                const d = btn.dataset;
                f.action = d.action;
                document.getElementById('jadwal-saya-form-method').value = 'PUT';
                f.elements['kelas_id'].value = d.kelas_id || '';
                isiMapelJadwalSaya(d.mata_pelajaran_id);
                tampilkanJadwalKelas();
                ['hari', 'jam_mulai', 'jam_selesai', 'ruang'].forEach(n => { f.elements[n].value = d[n] || ''; });
                document.getElementById('jadwal-saya-form-title').textContent = 'Edit Slot Jadwal';
                document.getElementById('jadwal-saya-submit').textContent = 'Simpan Perubahan';
                document.getElementById('jadwal-saya-batal').classList.remove('hidden');
                f.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function resetJadwalSayaForm() {
                const f = document.getElementById('jadwal-saya-form');
                f.reset();
                isiMapelJadwalSaya();
                tampilkanJadwalKelas();
                f.action = f.dataset.storeAction;
                document.getElementById('jadwal-saya-form-method').value = 'POST';
                document.getElementById('jadwal-saya-form-title').textContent = 'Tambah Slot Jadwal';
                document.getElementById('jadwal-saya-submit').textContent = 'Tambah Slot';
                document.getElementById('jadwal-saya-batal').classList.add('hidden');
            }
        </script>

        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <h6 class="font-bold text-slate-100 mb-3"><i class="fa-solid fa-list-ul mr-1 text-info"></i> Jadwal Mengajar Saya ({{ $jadwalSayaSorted->count() }})</h6>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400" style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                        <tr>
                            <th class="px-3 py-2">Hari</th>
                            <th class="px-3 py-2">Jam</th>
                            <th class="px-3 py-2">Kelas</th>
                            <th class="px-3 py-2">Mata Pelajaran</th>
                            <th class="px-3 py-2">Ruang</th>
                            <th class="px-3 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jadwalSayaSorted as $jadwal)
                            <tr>
                                <td class="px-3 py-1.5 font-semibold text-slate-100">{{ $jadwal->hari }}</td>
                                <td class="px-3 py-1.5">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                                <td class="px-3 py-1.5">{{ $jadwal->kelas->nama_kelas ?? '-' }}</td>
                                <td class="px-3 py-1.5">{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                                <td class="px-3 py-1.5"><span class="badge bg-secondary">{{ $jadwal->ruang }}</span></td>
                                <td class="px-3 py-1.5 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" onclick="editJadwalSaya(this)"
                                            data-action="{{ route('guru.jadwal-mengajar.update', $jadwal) }}"
                                            data-kelas_id="{{ $jadwal->kelas_id }}" data-mata_pelajaran_id="{{ $jadwal->mata_pelajaran_id }}"
                                            data-hari="{{ $jadwal->hari }}" data-jam_mulai="{{ substr($jadwal->jam_mulai, 0, 5) }}"
                                            data-jam_selesai="{{ substr($jadwal->jam_selesai, 0, 5) }}" data-ruang="{{ $jadwal->ruang }}"
                                            class="btn btn-outline-primary btn-xs py-0.5 px-2" style="font-size: 11px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <form method="POST" action="{{ route('guru.jadwal-mengajar.destroy', $jadwal) }}" onsubmit="return confirm('Hapus slot jadwal ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-xs py-0.5 px-2" style="font-size: 11px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-3 py-4 text-center text-slate-400">Belum ada jadwal mengajar yang Anda input.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
