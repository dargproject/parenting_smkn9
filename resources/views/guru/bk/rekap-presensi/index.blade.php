@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h4 class="font-bold text-slate-100 m-0 text-xl">Rekap Presensi Siswa</h4>
        <p class="text-slate-400 text-sm m-0">Presensi siswa hasil pengisian Jurnal &amp; Presensi oleh guru mapel. Khusus kehadiran, tidak menampilkan materi/isi jurnal.</p>
    </div>

    @include('admin.partials.flash')

    <form method="GET" class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-4 flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-400">Kelas</label>
            <select name="kelas_id" onchange="this.form.submit()" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
                <option value="">Pilih kelas...</option>
                @foreach($kelasList as $k)
                    <option value="{{ $k->id }}" @selected($kelasTerpilih?->id === $k->id)>{{ $k->nama_kelas }}{{ $kelasBinaanIds->contains($k->id) ? ' (Siswa Saya)' : '' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-400">Tanggal</label>
            <input type="date" name="tanggal" value="{{ $tanggal }}" max="{{ now()->toDateString() }}" onchange="this.form.submit()" class="rounded-lg border border-slate-600 bg-slate-900 px-3 py-2 text-sm text-slate-100">
        </div>
        <p class="text-xs text-slate-500 sm:ml-auto sm:self-end mb-0.5">Hari: <span class="font-semibold text-slate-300">{{ $namaHariDipilih }}</span></p>
    </form>

    @if(!$kelasTerpilih)
        <p class="text-slate-400 text-sm">Pilih kelas terlebih dahulu untuk melihat rekap presensinya.</p>
    @else
        <!-- Ringkasan Kehadiran per Siswa -->
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <h6 class="font-bold text-slate-100 mb-1"><i class="fa-solid fa-chart-simple mr-1 text-primary"></i> Ringkasan Kehadiran Semester &mdash; {{ $kelasTerpilih->nama_kelas }}</h6>
            <p class="text-slate-400 text-xs mb-3">"Per Mata Pelajaran" menjumlahkan tiap jadwal secara terpisah. "Per Hari" memberi satu status untuk satu hari: <b>Hadir/Sakit/Izin/Alpa Penuh</b> bila semua jadwal hari itu berstatus sama, atau <b>Campuran</b> bila hasilnya beda-beda.</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-400 table-bordered text-center" style="font-size: 13px;">
                    <thead class="bg-slate-900 border-b border-slate-700/60 text-xs align-middle text-slate-100">
                        <tr>
                            <th class="text-start" rowspan="2" style="min-width: 170px;">Nama Siswa</th>
                            <th colspan="5">Per Mata Pelajaran</th>
                            <th colspan="5">Per Hari</th>
                        </tr>
                        <tr>
                            <th>Hadir</th>
                            <th>Sakit</th>
                            <th>Izin</th>
                            <th>Alpa</th>
                            <th>Persentase</th>
                            <th>Hadir Penuh</th>
                            <th>Sakit Penuh</th>
                            <th>Izin Penuh</th>
                            <th>Campuran</th>
                            <th>Alpa Penuh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswaKelas as $siswa)
                            @php
                                $r = $rekapSemester[$siswa->id] ?? ['jumlah' => ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0], 'total' => 0, 'persen' => null];
                                $h = $rekapHarian[$siswa->id] ?? ['penuh' => 0, 'sakit' => 0, 'izin' => 0, 'sebagian' => 0, 'alpa' => 0, 'total_hari' => 0];
                            @endphp
                            <tr>
                                <td class="text-start font-bold text-slate-100">{{ $siswa->nama }}</td>
                                <td class="text-emerald-400 font-semibold">{{ $r['jumlah']['H'] }}</td>
                                <td class="text-cyan-400 font-semibold">{{ $r['jumlah']['S'] }}</td>
                                <td class="text-amber-400 font-semibold">{{ $r['jumlah']['I'] }}</td>
                                <td class="text-rose-400 font-semibold">{{ $r['jumlah']['A'] }}</td>
                                <td class="font-bold text-slate-100">{{ $r['persen'] !== null ? $r['persen'].'%' : '-' }}</td>
                                <td class="text-emerald-400 font-semibold border-l border-slate-700/60">{{ $h['penuh'] }}</td>
                                <td class="text-cyan-400 font-semibold">{{ $h['sakit'] }}</td>
                                <td class="text-amber-400 font-semibold">{{ $h['izin'] }}</td>
                                <td class="text-orange-400 font-semibold">{{ $h['sebagian'] }}</td>
                                <td class="text-rose-400 font-semibold">{{ $h['alpa'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="py-3 text-slate-400">Belum ada siswa aktif di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Harian -->
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/80 p-5 md:p-6 text-slate-100">
            <h6 class="font-bold text-slate-100 m-0 mb-3"><i class="fa-solid fa-table-cells mr-1 text-primary"></i> Detail Kehadiran &mdash; {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</h6>

            @if($jadwalHariDipilih->isEmpty())
                <p class="text-slate-400 text-sm m-0">Tidak ada jadwal pelajaran pada hari itu untuk kelas ini.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-400 table-bordered text-center" style="font-size: 13px;">
                        <thead class="bg-slate-900 border-b border-slate-700/60 text-xs align-middle text-slate-100">
                            <tr>
                                <th class="text-start" style="min-width: 170px;">Nama Siswa</th>
                                @foreach($jadwalHariDipilih as $jadwal)
                                    <th>{{ $jadwal->mataPelajaran->nama_mapel ?? '-' }}<br><span class="text-slate-400" style="font-size: 10px;">{{ substr($jadwal->jam_mulai, 0, 5) }} - {{ substr($jadwal->jam_selesai, 0, 5) }}</span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($siswaKelas as $siswa)
                                <tr>
                                    <td class="text-start font-bold text-slate-100">{{ $siswa->nama }}</td>
                                    @foreach($jadwalHariDipilih as $jadwal)
                                        @php $p = $presensiTanggalDipilih->first(fn ($x) => $x->siswa_id === $siswa->id && $x->jadwal_pelajaran_id === $jadwal->id); @endphp
                                        <td class="font-semibold {{ match($p->status ?? null) { 'H' => 'text-emerald-400', 'S' => 'text-cyan-400', 'I' => 'text-amber-400', 'A' => 'text-rose-400', default => 'text-slate-600' } }}">
                                            {{ $p->status ?? '-' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
