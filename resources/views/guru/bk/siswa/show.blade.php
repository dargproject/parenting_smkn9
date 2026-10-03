@extends('layouts.app')

@section('content')
@php
    $profil = $siswa->profilSiswa;
    $keluarga = $siswa->dataKeluarga;
@endphp
<div class="space-y-6">
    <div>
        <a href="javascript:history.back()" class="text-sm text-blue-400 hover:text-blue-300"><i class="fa-solid fa-arrow-left mr-1"></i> Kembali</a>
        <h4 class="font-bold text-slate-100 m-0 mt-1 text-xl">{{ $siswa->nama }}</h4>
        <p class="text-slate-400 text-sm m-0">{{ $siswa->kelas->nama_kelas ?? '-' }} &middot; NIS {{ $siswa->nis }} <span class="confidential-badge ml-2"><i class="fa-solid fa-shield-halved mr-1"></i>RAHASIA BK</span></p>
    </div>

    @include('admin.partials.flash')

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Data Pribadi --}}
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Data Pribadi</h6>
            <form method="POST" action="{{ route('guru.bk.siswa.profil.update', $siswa) }}" class="space-y-3">
                @csrf @method('PUT')
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-400">Alamat</label>
                    <textarea name="alamat" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('alamat', $profil->alamat ?? '') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" value="{{ old('tempat_lahir', $profil->tempat_lahir ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Agama</label>
                        <input type="text" name="agama" value="{{ old('agama', $profil->agama ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Anak Ke-</label>
                        <input type="number" min="1" name="anak_ke" value="{{ old('anak_ke', $profil->anak_ke ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Jumlah Saudara</label>
                        <input type="number" min="0" name="jml_saudara" value="{{ old('jml_saudara', $profil->jml_saudara ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-400">Asal SMP</label>
                    <input type="text" name="asal_smp" value="{{ old('asal_smp', $profil->asal_smp ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Hobi</label>
                        <input type="text" name="hobi" value="{{ old('hobi', $profil->hobi ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Bakat</label>
                        <input type="text" name="bakat" value="{{ old('bakat', $profil->bakat ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Rencana Setelah Lulus</label>
                        <select name="rencana_lulus" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                            <option value="">- Pilih -</option>
                            @foreach(['Bekerja', 'Kuliah', 'Menikah'] as $opt)
                                <option value="{{ $opt }}" @selected(old('rencana_lulus', $profil->rencana_lulus ?? '') === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Detail Rencana</label>
                        <input type="text" name="detail_rencana_lulus" value="{{ old('detail_rencana_lulus', $profil->detail_rencana_lulus ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Data Pribadi</button>
            </form>
        </div>

        {{-- Data Keluarga --}}
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Data Keluarga (Kumulatif Record)</h6>
            <form method="POST" action="{{ route('guru.bk.siswa.keluarga.update', $siswa) }}" class="space-y-3">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Nama Ayah</label>
                        <input type="text" name="nama_ayah" value="{{ old('nama_ayah', $keluarga->nama_ayah ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Nama Ibu</label>
                        <input type="text" name="nama_ibu" value="{{ old('nama_ibu', $keluarga->nama_ibu ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" value="{{ old('pekerjaan_ayah', $keluarga->pekerjaan_ayah ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" value="{{ old('pekerjaan_ibu', $keluarga->pekerjaan_ibu ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Pendidikan Ayah</label>
                        <input type="text" name="pendidikan_ayah" value="{{ old('pendidikan_ayah', $keluarga->pendidikan_ayah ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Pendidikan Ibu</label>
                        <input type="text" name="pendidikan_ibu" value="{{ old('pendidikan_ibu', $keluarga->pendidikan_ibu ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-400">No. Telp Orang Tua</label>
                    <input type="text" name="telp_ortu" value="{{ old('telp_ortu', $keluarga->telp_ortu ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Alamat Ayah</label>
                        <textarea name="alamat_ayah" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('alamat_ayah', $keluarga->alamat_ayah ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Alamat Ibu</label>
                        <textarea name="alamat_ibu" rows="2" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">{{ old('alamat_ibu', $keluarga->alamat_ibu ?? '') }}</textarea>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Status Rumah</label>
                        <input type="text" name="status_rumah" value="{{ old('status_rumah', $keluarga->status_rumah ?? '') }}" placeholder="Milik sendiri / sewa / dll." class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100 placeholder:text-slate-500">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-slate-400">Jumlah Kamar</label>
                        <input type="number" min="0" name="jml_kamar" value="{{ old('jml_kamar', $keluarga->jml_kamar ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="punya_kamar_sendiri" value="1" @checked(old('punya_kamar_sendiri', $keluarga->punya_kamar_sendiri ?? false)) class="rounded border-slate-600 bg-slate-900">
                    Memiliki kamar sendiri
                </label>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-400">Media Sosial</label>
                    <input type="text" name="media_sosial" value="{{ old('media_sosial', $keluarga->media_sosial ?? '') }}" class="w-full rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                </div>
                <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500">Simpan Data Keluarga</button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Kasus BK ({{ $kasusBks->count() }})</h6>
            <div class="flex flex-col gap-2">
                @forelse($kasusBks as $kasus)
                    <a href="{{ route('guru.bk.kasus.show', $kasus) }}" class="flex items-center justify-between rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm hover:border-blue-500">
                        <span class="text-slate-100">{{ $kasus->judul }}</span>
                        <span class="text-xs text-slate-400">{{ ucfirst($kasus->status) }}</span>
                    </a>
                @empty
                    <p class="text-slate-400 text-sm m-0">Tidak ada kasus BK milik Anda untuk siswa ini.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Layanan ({{ $layanan->count() }})</h6>
            <div class="flex flex-col gap-2">
                @forelse($layanan as $item)
                    <a href="{{ $item['route'] }}" class="flex items-center justify-between rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm hover:border-blue-500">
                        <span class="text-slate-100">{{ $item['jenis'] }}</span>
                        <span class="text-xs text-slate-400">{{ optional($item['tanggal'])->translatedFormat('d M Y') }}</span>
                    </a>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada layanan tercatat milik Anda untuk siswa ini.</p>
                @endforelse
            </div>
        </div>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5">
            <h6 class="font-bold text-slate-100 mb-3">Asesmen ({{ $asesmen->count() }})</h6>
            <div class="flex flex-col gap-2">
                @forelse($asesmen as $item)
                    <a href="{{ $item['route'] }}" class="flex items-center justify-between rounded-lg border border-slate-700/60 bg-slate-900 px-3 py-2 text-sm hover:border-blue-500">
                        <span class="text-slate-100">{{ $item['jenis'] }}</span>
                        <span class="text-xs text-slate-400">{{ optional($item['tanggal'])->translatedFormat('d M Y') }}</span>
                    </a>
                @empty
                    <p class="text-slate-400 text-sm m-0">Belum ada asesmen tercatat untuk siswa ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
