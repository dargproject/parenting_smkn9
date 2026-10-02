{{--
    Modal tambah/edit Kasus BK, dipakai di 3 tempat: halaman daftar Kasus BK, halaman detail Kasus BK,
    dan pane Kanban di portal. Dibuka/ditutup lewat toggle inline style display biasa (bukan Alpine
    lintas-elemen atau class Tailwind, supaya tidak ambigu soal urutan CSS) agar aman dipakai
    berkali-kali di halaman yang berbeda. Buka dengan: document.getElementById(formId).style.display='flex'.

    Variabel wajib: $formId (string unik), $siswas, $kategoriKasusList.
    Variabel opsional: $kasusBk (model, untuk mode edit).
--}}
@php
    $mode = isset($kasusBk) ? 'edit' : 'create';
    $action = $mode === 'edit' ? route('guru.bk.kasus.update', $kasusBk) : route('guru.bk.kasus.store');
@endphp
<div id="{{ $formId }}" class="fixed inset-0 z-[100] items-center justify-center p-4 bg-black/50" style="display: none;">
    <div class="solid-panel w-full max-w-lg rounded-2xl border border-slate-700/60 bg-slate-800/80 text-slate-100 shadow-xl max-h-[90vh] overflow-y-auto" x-data="{ step: 1 }">
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="p-5">
            @csrf
            @if($mode === 'edit')
                @method('PUT')
            @endif

            <div class="flex items-center justify-between mb-4">
                <h5 class="font-bold text-slate-100 m-0">{{ $mode === 'edit' ? 'Edit Kasus BK' : 'Kasus BK Baru' }}</h5>
                <button type="button" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="text-slate-400 hover:text-slate-100"><i class="fa-solid fa-xmark"></i></button>
            </div>

            <div class="mb-4">
                <p class="text-xs font-bold text-blue-400 mb-1.5" x-text="step === 1 ? 'Langkah 1 dari 2: Data Kasus' : 'Langkah 2 dari 2: Lampiran'"></p>
                <div class="flex gap-1.5">
                    <div class="h-1.5 w-1/2 rounded-full bg-blue-600"></div>
                    <div class="h-1.5 w-1/2 rounded-full" :class="step === 2 ? 'bg-blue-600' : 'bg-slate-700'"></div>
                </div>
            </div>

            <div x-show="step === 1" class="space-y-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Siswa</label>
                    @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas, 'default' => $kasusBk->siswa_id ?? null])
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Judul / Penanganan</label>
                    <input type="text" name="judul" required maxlength="255" value="{{ old('judul', $kasusBk->judul ?? '') }}" placeholder="Mis. Konseling Individu, Rujukan Wali Kelas..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Kategori</label>
                        <select name="kategori_id" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                            <option value="">Pilih kategori (opsional)</option>
                            @foreach($kategoriKasusList as $kategori)
                                <option value="{{ $kategori->id }}" @selected(old('kategori_id', $kasusBk->kategori_id ?? null) == $kategori->id)>{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-400">Prioritas</label>
                        <select name="prioritas" required class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                            @foreach(['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('prioritas', $kasusBk->prioritas ?? 'rendah') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" required value="{{ old('tanggal_mulai', optional($kasusBk->tanggal_mulai ?? null)->toDateString() ?? now()->toDateString()) }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Deskripsi / Uraian Masalah</label>
                    <textarea name="deskripsi" rows="3" required placeholder="Gambaran umum masalah yang dihadapi siswa..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ old('deskripsi', $kasusBk->deskripsi ?? '') }}</textarea>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tindak Lanjut <span class="font-normal">(opsional)</span></label>
                    <textarea name="tindak_lanjut" rows="2" placeholder="Hasil layanan dan langkah tindak lanjut..." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">{{ old('tindak_lanjut', $kasusBk->tindak_lanjut ?? '') }}</textarea>
                </div>
            </div>

            <div x-show="step === 2" x-cloak class="space-y-3">
                @if($mode === 'edit' && $kasusBk->lampiran->isNotEmpty())
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-400">Lampiran Tersimpan</label>
                        <div class="flex flex-col gap-2">
                            @foreach($kasusBk->lampiran as $lampiran)
                                <label class="flex items-center justify-between gap-2 rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm text-slate-300">
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($lampiran->path_file) }}" target="_blank" class="truncate text-blue-400 hover:text-blue-300">{{ $lampiran->nama_file }}</a>
                                    <span class="flex items-center gap-1.5 text-xs text-rose-400 whitespace-nowrap">
                                        <input type="checkbox" name="lampiran_dihapus[]" value="{{ $lampiran->id }}" class="rounded border-slate-600 bg-slate-900"> Hapus
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-400">Tambah Lampiran <span class="font-normal">(maks. 5 file, 12MB/file: pdf, jpg, png, docx)</span></label>
                    <input type="file" name="lampiran[]" multiple accept=".pdf,.jpg,.jpeg,.png,.docx" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
            </div>

            <div class="mt-5 flex justify-end gap-2">
                <button type="button" x-show="step === 1" onclick="document.getElementById('{{ $formId }}').style.display='none'" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Batal</button>
                <button type="button" x-show="step === 1" @click="step = 2" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Lanjut: Lampiran</button>
                <button type="button" x-show="step === 2" x-cloak @click="step = 1" class="rounded-lg border border-slate-600 px-4 py-2 text-sm font-semibold text-slate-300">Kembali</button>
                <button type="submit" x-show="step === 2" x-cloak class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">{{ $mode === 'edit' ? 'Perbarui Kasus' : 'Simpan Kasus' }}</button>
            </div>
        </form>
    </div>
</div>
