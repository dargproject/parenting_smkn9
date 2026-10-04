@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">Nilai Ekstrakurikuler</h4>
        <p class="text-slate-400 text-sm">Kelola peserta dan nilai (A/B/C) untuk ekstrakurikuler yang Anda bina.</p>
    </div>

    @include('admin.partials.flash')

    @if(!$tahunAjaranAktif)
        <div class="rounded-xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-400">Belum ada tahun ajaran aktif. Hubungi admin untuk mengaktifkan tahun ajaran terlebih dahulu.</div>
    @elseif($ekstrakurikulers->isEmpty())
        <p class="text-slate-400 text-sm">Anda belum ditetapkan sebagai pembina ekstrakurikuler apapun. Hubungi Admin.</p>
    @else
        @foreach($ekstrakurikulers as $ekskul)
            <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 space-y-4">
                <h5 class="text-lg font-bold text-slate-100">{{ $ekskul->nama_ekskul }}</h5>

                <form method="POST" action="{{ route('guru.ekskul.siswa.store') }}" class="flex flex-col sm:flex-row gap-2 items-start">
                    @csrf
                    <input type="hidden" name="ekstrakurikuler_id" value="{{ $ekskul->id }}">
                    <div class="flex-1 w-full">
                        @include('kesiswaan.partials.siswa-search', ['name' => 'siswa_id', 'siswas' => $siswas])
                    </div>
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-500 whitespace-nowrap">+ Tambah Peserta</button>
                </form>

                @if($ekskul->roster->isEmpty())
                    <p class="text-slate-400 text-sm m-0">Belum ada siswa terdaftar di ekstrakurikuler ini.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs text-slate-500">
                                <tr>
                                    <th class="py-2 pr-2">Nama Siswa</th>
                                    <th class="py-2 px-2">Kelas</th>
                                    <th class="py-2 px-2">Nilai</th>
                                    <th class="py-2 px-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/60">
                                @foreach($ekskul->roster as $peserta)
                                    @php $nilai = $ekskul->nilaiMap[$peserta->siswa_id] ?? null; @endphp
                                    <tr>
                                        <td class="py-2 pr-2 font-semibold text-slate-100">{{ $peserta->siswa->nama ?? '-' }}</td>
                                        <td class="py-2 px-2 text-slate-400">{{ $peserta->siswa->kelas->nama_kelas ?? '-' }}</td>
                                        <td class="py-2 px-2">
                                            <form method="POST" action="{{ route('guru.ekskul.nilai.store') }}" class="flex items-center gap-2">
                                                @csrf
                                                <input type="hidden" name="ekstrakurikuler_id" value="{{ $ekskul->id }}">
                                                <input type="hidden" name="siswa_id" value="{{ $peserta->siswa_id }}">
                                                <select name="nilai" onchange="this.form.submit()" class="rounded-lg border border-slate-600 bg-slate-900 px-2 py-1.5 text-sm text-slate-100">
                                                    <option value="" disabled {{ !$nilai ? 'selected' : '' }}>Pilih</option>
                                                    @foreach(['A', 'B', 'C'] as $opsi)
                                                        <option value="{{ $opsi }}" @selected($nilai?->nilai === $opsi)>{{ $opsi }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </td>
                                        <td class="py-2 px-2 text-right">
                                            <form method="POST" action="{{ route('guru.ekskul.siswa.destroy', $peserta) }}" onsubmit="return confirm('Keluarkan {{ $peserta->siswa->nama ?? 'siswa ini' }} dari ekstrakurikuler ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="text-xs text-rose-400 hover:text-rose-300">Keluarkan</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>
@endsection
