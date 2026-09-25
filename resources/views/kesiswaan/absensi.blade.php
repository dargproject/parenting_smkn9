<div id="pane-kesiswaan-absensi" class="pane-content hidden-pane fade-transition">
    <h4 class="font-bold text-slate-100 mb-2">Data Absensi Bermasalah</h4>
    <p class="text-slate-400 small mb-4">Daftar siswa dengan akumulasi ketidakhadiran (Alpa) tercatat lebih dari 0 kali.</p>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-400">
                <thead class="bg-slate-900 border-b border-slate-700/60 text-xs">
                    <tr>
                        <th class="px-2 py-2">Nama Siswa</th>
                        <th class="px-2 py-2 text-center">Kelas</th>
                        <th class="px-2 py-2 text-center">Total Alpa</th>
                        <th class="px-2 py-2">Status Peringatan</th>
                        <th class="px-2 py-2 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody id="kesiswaan-absensi-tbody">
                    @forelse($absensiBermasalah as $siswa)
                        <tr class="border-b border-slate-700/40">
                            <td class="px-2 py-2 font-bold">{{ $siswa->nama }} <span class="block text-slate-400 small" style="font-size: 10px;">NIS: {{ $siswa->nis }}</span></td>
                            <td class="px-2 py-2 text-center">{{ $siswa->kelas->nama_kelas ?? '-' }}</td>
                            <td class="px-2 py-2 text-center font-bold text-rose-400 fs-5">{{ $siswa->alpa_count }}</td>
                            <td class="px-2 py-2">
                                @if($siswa->alpa_count >= 5)
                                    <span class="badge bg-rose-500/10 text-rose-400 border border-rose-500/20 badge-pill-custom">SP 1 (Panggilan Ortu)</span>
                                @else
                                    <span class="badge bg-amber-500/10 text-amber-400 border border-amber-500/20 badge-pill-custom">Teguran Keras</span>
                                @endif
                            </td>
                            <td class="px-2 py-2 text-end">
                                <a href="#" class="btn btn-outline-danger btn-sm rounded-lg" onclick="showPane('pane-kesiswaan-ortu', document.querySelector('#sidebar-menu-list a[onclick*=\'pane-kesiswaan-ortu\']')); return false;">
                                    <i class="fa-regular fa-envelope mr-1"></i> Panggil Ortu
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-2 py-8 text-center text-slate-500">Tidak ada siswa dengan catatan Alpa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
