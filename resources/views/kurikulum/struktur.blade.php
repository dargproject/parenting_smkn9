<div id="pane-kurikulum-struktur" class="pane-content hidden-pane fade-transition">
    @php
        $kmSorted = $kelasMapelSemua->sortBy(fn ($km) => ($km->kelas->nama_kelas ?? '').'|'.($km->mataPelajaran->nama_mapel ?? ''), SORT_NATURAL)->values();
        $kmPerKelas = $kelasMapelSemua->groupBy('kelas_id')->map(fn ($g) => $g->map(fn ($km) => ['id' => $km->mata_pelajaran_id, 'label' => ($km->mataPelajaran->nama_mapel ?? '-').' ('.($km->guru->nama ?? 'guru belum dipilih').')'])->sortBy('label')->values());
        $guruPilihan = \App\Models\Guru::whereDoesntHave('roles', fn ($q) => $q->whereIn('name', ['admin', 'kepsek']))->orderBy('nama')->get(['id', 'nama']);
    @endphp
    <h4 class="font-bold text-slate-100 mb-2">Struktur Kurikulum Merdeka</h4>
    <p class="text-slate-400 small mb-4">Tetapkan mata pelajaran dan guru pengampu untuk tiap kelas, lalu susun jadwal KBM dari mapel yang sudah ditetapkan. Data master mapel (kode, kategori, beban JP) dikelola di Admin &rarr; Mata Pelajaran.</p>

    <!-- 1. Mapel per Kelas -->
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-6 text-slate-100 mb-4" x-data="{ filter: '' }">
        <h6 class="font-bold text-slate-100 mb-3"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white mr-1">1</span> Mapel per Kelas</h6>
        <form method="POST" action="{{ route('guru.kurikulum.kelas-mapel.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Kelas</label>
                <select name="kelas_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih kelas...</option>
                    @foreach($kelasList->sortBy('nama_kelas', SORT_NATURAL) as $k)
                        <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                <label class="mt-3 mb-1.5 block text-sm font-medium text-slate-400">Guru Pengampu</label>
                <select name="guru_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih guru pengampu...</option>
                    @foreach($guruPilihan as $g)
                        <option value="{{ $g->id }}">{{ $g->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Mata Pelajaran <span class="font-normal">(boleh pilih lebih dari satu)</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-1 max-h-44 overflow-y-auto rounded-lg border border-slate-600 bg-slate-900 p-2">
                    @forelse($mataPelajarans->sortBy('nama_mapel') as $mapel)
                        <label class="flex items-center gap-2 rounded px-2 py-1 text-sm text-slate-100 hover:bg-slate-700/30 cursor-pointer">
                            <input type="checkbox" name="mata_pelajaran_id[]" value="{{ $mapel->id }}" class="h-4 w-4">
                            <span>{{ $mapel->nama_mapel }} <span class="text-slate-400 text-xs">({{ $mapel->kategori }})</span></span>
                        </label>
                    @empty
                        <p class="text-sm text-slate-400 p-2">Belum ada mata pelajaran. Tambahkan di Admin &rarr; Mata Pelajaran.</p>
                    @endforelse
                </div>
            </div>
            <div class="md:col-span-3 flex justify-end">
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-500">Tetapkan ke Kelas</button>
            </div>
        </form>

        <div class="flex items-center justify-between gap-3 mb-2">
            <span class="text-sm font-semibold text-slate-100">Struktur yang sudah ditetapkan ({{ $kmSorted->count() }})</span>
            <select x-model="filter" class="rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-xs text-slate-100">
                <option value="">Semua kelas</option>
                @foreach($kelasList->sortBy('nama_kelas', SORT_NATURAL) as $k)
                    <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                @endforeach
            </select>
        </div>
        <div class="overflow-x-auto max-h-72 overflow-y-auto rounded-xl border border-slate-700/60">
            <table class="w-full min-w-[560px] text-left text-sm">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400 sticky top-0">
                    <tr><th class="px-3 py-2">Kelas</th><th class="px-3 py-2">Mata Pelajaran</th><th class="px-3 py-2">Guru Pengampu</th><th class="px-3 py-2 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-700/60">
                    @forelse($kmSorted as $km)
                        <tr x-show="!filter || filter === '{{ $km->kelas_id }}'">
                            <td class="px-3 py-1.5 font-semibold text-slate-100 whitespace-nowrap">{{ $km->kelas->nama_kelas ?? '-' }}</td>
                            <td class="px-3 py-1.5">{{ $km->mataPelajaran->nama_mapel ?? '-' }}<span class="text-slate-400 text-xs"> &middot; {{ $km->mataPelajaran->kategori ?? '' }}</span></td>
                            <td class="px-3 py-1.5">
                                <form method="POST" action="{{ route('guru.kurikulum.kelas-mapel.update', $km) }}" class="flex items-center gap-1">
                                    @csrf @method('PUT')
                                    <select name="guru_id" onchange="this.form.requestSubmit()" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-2 py-1 text-xs text-slate-100">
                                        @unless($km->guru_id)<option value="">— pilih guru —</option>@endunless
                                        @foreach($guruPilihan as $g)
                                            <option value="{{ $g->id }}" @selected($km->guru_id == $g->id)>{{ $g->nama }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td class="px-3 py-1.5 text-center">
                                <form method="POST" action="{{ route('guru.kurikulum.kelas-mapel.destroy', $km) }}" onsubmit="return confirm('Hapus mapel ini dari kelas?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-xs py-0.5 px-2" style="font-size: 11px;" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-3 py-4 text-center text-slate-400">Belum ada mapel yang ditetapkan ke kelas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 2. Jadwal -->
    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 md:p-6 text-slate-100 mb-4">
        <h6 class="font-bold text-slate-100 mb-3"><span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-blue-600 text-xs text-white mr-1">2</span> <span id="jadwal-form-title">Input Sebaran Jadwal Pelajaran (KBM)</span></h6>
        <form id="jadwal-form" method="POST" action="{{ route('guru.kurikulum.jadwal.store') }}" data-store-action="{{ route('guru.kurikulum.jadwal.store') }}" data-old-mapel="{{ old('mata_pelajaran_id') }}" class="grid grid-cols-1 md:grid-cols-3 gap-3">
            @csrf
            <input type="hidden" name="_method" value="POST" id="jadwal-form-method">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Kelas</label>
                <select name="kelas_id" required onchange="isiMapelJadwal()" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih kelas...</option>
                    @foreach($kelasList->sortBy('nama_kelas', SORT_NATURAL) as $k)
                        <option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-slate-400">Mata Pelajaran (Guru Pengampu)</label>
                <select name="mata_pelajaran_id" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    <option value="">Pilih kelas dulu...</option>
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
        const KELAS_MAPEL = @json($kmPerKelas);
        function isiMapelJadwal(pilihId) {
            const f = document.getElementById('jadwal-form');
            const sel = f.elements['mata_pelajaran_id'];
            const kelasId = f.elements['kelas_id'].value;
            const daftar = KELAS_MAPEL[kelasId] || [];
            sel.innerHTML = '';
            const ph = document.createElement('option');
            ph.value = '';
            ph.textContent = kelasId ? (daftar.length ? 'Pilih mata pelajaran...' : 'Belum ada mapel ditetapkan untuk kelas ini') : 'Pilih kelas dulu...';
            sel.appendChild(ph);
            daftar.forEach(m => { const o = document.createElement('option'); o.value = m.id; o.textContent = m.label; sel.appendChild(o); });
            if (pilihId) sel.value = String(pilihId);
        }
        document.addEventListener('DOMContentLoaded', () => isiMapelJadwal(document.getElementById('jadwal-form').dataset.oldMapel));

        function editJadwal(btn) {
            const f = document.getElementById('jadwal-form');
            const d = btn.dataset;
            f.action = d.action;
            document.getElementById('jadwal-form-method').value = 'PUT';
            f.elements['kelas_id'].value = d.kelas_id || '';
            isiMapelJadwal(d.mata_pelajaran_id);
            ['hari', 'jam_mulai', 'jam_selesai', 'ruang'].forEach(n => { f.elements[n].value = d[n] || ''; });
            document.getElementById('jadwal-form-title').textContent = 'Edit Jadwal Pelajaran';
            document.getElementById('jadwal-submit').textContent = 'Simpan Perubahan';
            document.getElementById('jadwal-batal').classList.remove('hidden');
            f.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        function resetJadwalForm() {
            const f = document.getElementById('jadwal-form');
            f.reset();
            isiMapelJadwal();
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

</div>
