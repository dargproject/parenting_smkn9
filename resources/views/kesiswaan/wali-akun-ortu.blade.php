<div id="pane-wali-akun-ortu" class="pane-content hidden-pane fade-transition">
    <div class="mb-3">
        <h4 class="font-bold text-slate-100 m-0">Persetujuan Akun Orang Tua</h4>
        <p class="text-slate-400 small m-0">Orang tua mendaftar sendiri lewat halaman login, lalu menunggu persetujuan Anda sebelum bisa login -- karena tidak ada data yang bisa dicocokkan otomatis untuk memverifikasi siapa pendaftarnya. Lupa password juga bisa direset di sini.</p>
    </div>

    <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 shadow-sm overflow-x-auto">
        <table class="w-full min-w-[700px] text-left text-sm">
            <thead class="bg-slate-900 border-b border-slate-700/60 text-xs text-slate-400">
                <tr>
                    <th class="px-5 py-3">Nama Orang Tua</th>
                    <th class="px-5 py-3">No. HP</th>
                    <th class="px-5 py-3">Siswa</th>
                    <th class="px-5 py-3">Kelas</th>
                    <th class="px-5 py-3 text-center">Status</th>
                    <th class="px-5 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/60">
                @forelse($akunOrtuWali as $akun)
                    <tr>
                        <td class="px-5 py-3 font-semibold text-slate-100">{{ $akun->orangTua->nama }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $akun->orangTua->username }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $akun->siswa->nama ?? '-' }}</td>
                        <td class="px-5 py-3 text-slate-400">{{ $akun->siswa->kelas->nama_kelas ?? '-' }}</td>
                        <td class="px-5 py-3 text-center">
                            <x-status-badge :tone="$akun->is_active ? 'emerald' : 'amber'">{{ $akun->is_active ? 'Aktif' : 'Menunggu Persetujuan' }}</x-status-badge>
                        </td>
                        <td class="px-5 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                @if($akun->is_active)
                                    <form method="POST" action="{{ route('guru.wali-kelas.akun-ortu.reset-password', $akun->siswa) }}" onsubmit="return confirm('Reset password akun orang tua ini ke \'password\'? Orang tua wajib menggantinya saat login berikutnya.')">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-blue-500/40 px-3 py-1 text-xs font-semibold text-blue-400 hover:bg-blue-500/10">
                                            <i class="fa-solid fa-key mr-1"></i> Reset Password
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('guru.wali-kelas.akun-ortu.setujui', $akun->siswa) }}" onsubmit="return confirm('Setujui akun orang tua ini? Pastikan Anda memang kenal dengan pendaftarnya.')">
                                        @csrf
                                        <button type="submit" class="rounded-lg border border-emerald-500/40 px-3 py-1 text-xs font-semibold text-emerald-400 hover:bg-emerald-500/10">
                                            <i class="fa-solid fa-check mr-1"></i> Setujui
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('guru.wali-kelas.akun-ortu.tolak', $akun->siswa) }}" onsubmit="return confirm('Tolak pendaftaran akun orang tua ini? Pendaftarannya akan dihapus.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-rose-500/40 px-3 py-1 text-xs font-semibold text-rose-400 hover:bg-rose-500/10">
                                            <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-500">Belum ada akun orang tua untuk siswa binaan Anda.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
