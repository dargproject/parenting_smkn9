{{--
    Modal "Buat Panggilan Ortu", dipakai dari halaman detail Kunjungan Rumah & Konferensi Kasus.
    Reuse tabel panggilan_ortus yang sudah ada (dipakai juga oleh Waka Kesiswaan) -- di sini guru BK
    cukup diberi jalur terpisah untuk membuatnya, tanpa mengubah izin route waka_kesiswaan yang sudah ada.

    Variabel wajib: $formId, $siswaId. Variabel opsional: $alasanDefault.
--}}
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-md rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl">
        <form method="POST" action="{{ route('guru.bk.panggilan-ortu.store') }}" class="p-5">
            @csrf
            <input type="hidden" name="siswa_id" value="{{ $siswaId }}">

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">Buat Panggilan Orang Tua</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal</label>
                    <input type="date" name="tanggal" required value="{{ now()->toDateString() }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Waktu</label>
                    <input type="time" name="waktu" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Ruang <span class="font-normal">(opsional)</span></label>
                    <input type="text" name="ruang" placeholder="Misal: Ruang BK" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Alasan</label>
                    <textarea name="alasan" rows="3" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ $alasanDefault ?? '' }}</textarea>
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Jadwalkan</button>
            </div>
        </form>
    </div>
</div>
