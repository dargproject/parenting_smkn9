<div id="pane-kurikulum-struktur" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-2">Struktur Kurikulum Merdeka</h4>
    <p class="text-slate-400 small mb-4">Kelola sebaran jadwal KBM (kelas, mata pelajaran, hari, jam, ruang). Beban JP dan guru pengampu tiap mapel dikelola di Admin &rarr; Mata Pelajaran.</p>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-6 text-slate-100 mb-4">
        <h6 class="font-bold text-slate-100 mb-3"><i class="fa-regular fa-clock text-primary mr-1"></i> <span id="jadwal-form-title">Input Sebaran Jadwal Pelajaran (KBM)</span></h6>
        <form id="jadwal-form" method="POST" action="{{ route('guru.kurikulum.jadwal.store') }}" data-store-action="{{ route('guru.kurikulum.jadwal.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <input type="hidden" name="_method" value="POST" id="jadwal-form-method">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Kelas</label>
                <select name="kelas_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih kelas...</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Mata Pelajaran (Guru Pengampu)</label>
                <select name="mata_pelajaran_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih mata pelajaran...</option>
                    @foreach($mataPelajarans as $mapel)
                        <option value="{{ $mapel->id }}" @selected(old('mata_pelajaran_id') == $mapel->id)>{{ $mapel->nama_mapel }} ({{ $mapel->guru->nama ?? 'Belum ada guru' }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Hari</label>
                <select name="hari" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    @foreach(['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $hari)
                        <option value="{{ $hari }}" @selected(old('hari') === $hari)>{{ $hari }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Jam Mulai</label>
                <input type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Jam Selesai</label>
                <input type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Ruang</label>
                <input type="text" name="ruang" value="{{ old('ruang') }}" placeholder="mis. Lab RPS 1" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
            </div>
            <div class="md:col-span-3 flex justify-end gap-2">
                <button type="button" id="jadwal-batal" onclick="resetJadwalForm()" class="hidden rounded-lg border border-slate-600 px-4 py-2.5 text-sm font-semibold text-slate-300 hover:border-blue-500">Batal</button>
                <button type="submit" id="jadwal-submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Terbitkan Slot Jadwal</button>
            </div>
        </form>
    </div>
    <script>
        function editJadwal(btn) {
            const f = document.getElementById('jadwal-form');
            const d = btn.dataset;
            f.action = d.action;
            document.getElementById('jadwal-form-method').value = 'PUT';
            ['kelas_id', 'mata_pelajaran_id', 'hari', 'jam_mulai', 'jam_selesai', 'ruang'].forEach(n => { f.elements[n].value = d[n] || ''; });
            document.getElementById('jadwal-form-title').textContent = 'Edit Jadwal Pelajaran';
            document.getElementById('jadwal-submit').textContent = 'Simpan Perubahan';
            document.getElementById('jadwal-batal').classList.remove('hidden');
            f.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function resetJadwalForm() {
            const f = document.getElementById('jadwal-form');
            f.reset();
            f.action = f.dataset.storeAction;
            document.getElementById('jadwal-form-method').value = 'POST';
            document.getElementById('jadwal-form-title').textContent = 'Input Sebaran Jadwal Pelajaran (KBM)';
            document.getElementById('jadwal-submit').textContent = 'Terbitkan Slot Jadwal';
            document.getElementById('jadwal-batal').classList.add('hidden');
        }
    </script>

    <!-- List of active schedules -->
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100 mb-4">
        <h6 class="font-bold text-slate-100 mb-3"><i
                class="fa-solid fa-list-ul mr-1 text-info"></i> Daftar Sebaran Jadwal Aktif</h6>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400 table-sm" style="font-size: 13px;">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                    <tr>
                        <th>Guru Pengampu</th>
                        <th>Hari</th>
                        <th>Jam Ke / Waktu</th>
                        <th>Kelas Rombel</th>
                        <th>Mata Pelajaran</th>
                        <th>Ruang/Lab</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="schedules-list-tbody">
                    @foreach($jadwalPelajarans as $jadwal)
                        <tr>
                            <td class="font-bold">{{ $jadwal->guru->nama ?? '-' }}</td>
                            <td>{{ $jadwal->hari }}</td>
                            <td>{{ $jadwal->jam_mulai }} - {{ $jadwal->jam_selesai }}</td>
                            <td>{{ $jadwal->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}</td>
                            <td><span class="badge bg-secondary">{{ $jadwal->ruang }}</span></td>
                            <td class="text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" onclick="editJadwal(this)"
                                        data-action="{{ route('guru.kurikulum.jadwal.update', $jadwal) }}"
                                        data-kelas_id="{{ $jadwal->kelas_id }}" data-mata_pelajaran_id="{{ $jadwal->mata_pelajaran_id }}"
                                        data-hari="{{ $jadwal->hari }}" data-jam_mulai="{{ substr($jadwal->jam_mulai, 0, 5) }}"
                                        data-jam_selesai="{{ substr($jadwal->jam_selesai, 0, 5) }}" data-ruang="{{ $jadwal->ruang }}"
                                        class="btn btn-outline-primary btn-xs py-0.5 px-2" style="font-size: 11px;" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <form method="POST" action="{{ route('guru.kurikulum.jadwal.destroy', $jadwal) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-xs py-0.5 px-2" style="font-size: 11px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                    <tr>
                        <th>Kategori / Mata Pelajaran</th>
                        <th class="text-center">Beban JP</th>
                        <th>Guru Pengampu</th>
                        <th class="text-center">Status RPS</th>
                    </tr>
                </thead>
                <tbody id="curriculum-structure-tbody">
                    @php
                        $nasional = $mataPelajarans->where('kategori', 'Nasional');
                        $kejuruan = $mataPelajarans->where('kategori', 'Kejuruan');
                    @endphp

                    @if($nasional->count() > 0)
                        <tr class="bg-slate-900 border-b border-slate-700/60 text-xs">
                            <td colspan="4" class="font-bold text-slate-100">A. Muatan Nasional</td>
                        </tr>
                        @foreach($nasional as $mapel)
                            <tr>
                                <td class="ps-4">{{ $mapel->nama_mapel }}</td>
                                <td class="text-center">{{ $mapel->beban_jp }} JP</td>
                                <td>{{ $mapel->guru->nama ?? '-' }}</td>
                                <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
                            </tr>
                        @endforeach
                    @endif

                    @if($kejuruan->count() > 0)
                        <tr class="bg-slate-900 border-b border-slate-700/60 text-xs">
                            <td colspan="4" class="font-bold text-slate-100">B. Muatan Kejuruan</td>
                        </tr>
                        @foreach($kejuruan as $mapel)
                            <tr>
                                <td class="ps-4">{{ $mapel->nama_mapel }}</td>
                                <td class="text-center">{{ $mapel->beban_jp }} JP</td>
                                <td>{{ $mapel->guru->nama ?? '-' }}</td>
                                <td class="text-center"><i class="fa-solid fa-circle-check text-success"></i></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
