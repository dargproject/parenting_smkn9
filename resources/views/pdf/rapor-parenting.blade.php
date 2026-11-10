<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rapor Parenting - {{ $siswa->nama }}</title>
    @include('pdf.partials.rapor-style2')
</head>
<body>
    @include('pdf.partials.rapor-header2')

    <div class="section-title">II. Capaian Akademik &amp; Keterampilan Kejuruan</div>
    <table class="table-rapor">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 32%;">Aspek Penilaian</th>
                <th style="width: 63%;">Nilai &amp; Status Catatan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center font-bold">1</td>
                <td class="font-bold">Progres Akademik &amp; Teori</td>
                <td>
                    @forelse($akademikPerMapel as $a)
                        <div class="card-item bg-amber-light">
                            <strong>{{ $a['nama'] }}</strong><br>
                            <span class="badge-mini badge-warning">
                                Perlu peningkatan
                                @if (!empty($a['materi']))
                                    pada materi: {{ implode(', ', $a['materi']) }}
                                @endif
                            </span>
                        </div>
                    @empty
                        <span class="text-muted italic">Tidak ada mata pelajaran yang memerlukan peningkatan saat ini.</span>
                    @endforelse
                </td>
            </tr>

            <tr>
                <td class="text-center font-bold">2</td>
                <td class="font-bold">Kedisiplinan &amp; Kehadiran</td>
                <td>
                    @if(isset($totalPresensi) && $totalPresensi > 0)
                        <div>
                            <strong>{{ $persenHadir }}%</strong> hadir dari {{ $totalPresensi }} pertemuan &mdash;
                            Sakit: {{ $rekapKehadiran['S'] ?? 0 }}, Izin: {{ $rekapKehadiran['I'] ?? 0 }},
                            <span class="text-danger font-bold">Alpa: {{ $rekapKehadiran['A'] ?? 0 }}</span>.
                        </div>
                    @else
                        <div class="text-muted italic">Belum ada data presensi semester ini.</div>
                    @endif

                    <div class="mt-1">
                        <span class="badge badge-status-{{ ($statusSanksi['tone'] ?? '') === 'emerald' ? 'aman' : (($statusSanksi['tone'] ?? '') === 'amber' ? 'sp' : 'berat') }}">
                            {{ $statusSanksi['label'] ?? 'Normal' }}
                        </span>
                    </div>

                    @if(isset($ketidakhadiranPerMapel) && $ketidakhadiranPerMapel->isNotEmpty())
                        <div class="sub-block">
                            <div class="sub-block-title">Tidak Hadir per Mata Pelajaran:</div>
                            <ul class="custom-list">
                                @foreach($ketidakhadiranPerMapel as $m)
                                    <li>
                                        <strong>{{ $m['nama'] }}:</strong>
                                        @if(($m['alpa'] ?? 0) > 0) <span class="badge-mini badge-danger">Alpa {{ $m['alpa'] }}</span> @endif
                                        @if(($m['sakit'] ?? 0) > 0) <span class="badge-mini badge-info">Sakit {{ $m['sakit'] }}</span> @endif
                                        @if(($m['izin'] ?? 0) > 0) <span class="badge-mini badge-warning">Izin {{ $m['izin'] }}</span> @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(isset($pelanggaranList) && $pelanggaranList->isNotEmpty())
                        <div class="sub-block">
                            <div class="sub-block-title text-danger">Catatan Kedisiplinan:</div>
                            <ul class="custom-list">
                                @foreach($pelanggaranList as $p)
                                    <li>
                                        <span class="date-tag">{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }}</span>
                                        <strong>{{ $p->judul }}</strong>
                                        <span class="text-danger font-bold">({{ $p->poin }} poin)</span>
                                        @if($p->tindakan) &mdash; <span class="italic">{{ $p->tindakan }}</span> @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">III. Perkembangan Non-Akademik &amp; Karakter</div>
    <table class="table-rapor">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 32%;">Aspek Karakter &amp; Kegiatan</th>
                <th style="width: 63%;">Catatan Perkembangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center font-bold">1</td>
                <td class="font-bold">Kegiatan Ekstrakurikuler / Organisasi</td>
                <td>
                    @forelse($nilaiEkskul as $ne)
                        <div class="card-item">
                            <strong>{{ $ne->ekstrakurikuler->nama ?? '-' }}</strong> &mdash;
                            <span class="badge-mini badge-info">Nilai {{ $ne->nilai }}</span>
                            @if($ne->catatan) <span class="text-muted italic">({{ $ne->catatan }})</span> @endif
                        </div>
                    @empty
                        <span class="text-muted italic">Tidak mengikuti ekstrakurikuler yang tercatat.</span>
                    @endforelse
                </td>
            </tr>

            <tr>
                <td class="text-center font-bold">2</td>
                <td class="font-bold">Soft Skills, Komunikasi &amp; Karakter</td>
                <td>{{ $catatanKarakter ?: '-' }}</td>
            </tr>

            <tr>
                <td class="text-center font-bold">3</td>
                <td class="font-bold">Bakat &amp; Minat (Tes BK)</td>
                <td>
                    @if(isset($peminatan) && $peminatan)
                        <div>
                            @foreach(collect([$peminatan->pilihan1, $peminatan->pilihan2, $peminatan->pilihan3])->filter() as $pilihan)
                                <span class="tag-item">{{ $pilihan }}</span>
                            @endforeach
                        </div>
                        @if($peminatan->catatan)
                            <div class="text-muted italic mt-1">{{ $peminatan->catatan }}</div>
                        @endif
                    @else
                        <span class="text-muted italic">Belum ada data Tes Peminatan BK.</span>
                    @endif
                </td>
            </tr>

            <tr>
                <td class="text-center font-bold">4</td>
                <td class="font-bold">Prestasi Non-Akademik</td>
                <td>
                    @forelse($prestasiNonAkademik as $prestasi)
                        <div class="card-item bg-emerald-light">
                            <strong class="text-emerald">{{ $prestasi->nama_prestasi }}</strong>
                            <span class="text-muted">({{ $prestasi->tingkat }})</span> &mdash;
                            {{ \Carbon\Carbon::parse($prestasi->tanggal)->translatedFormat('d M Y') }}
                            @if($prestasi->peringkat) &bull; <strong>{{ $prestasi->peringkat }}</strong> @endif
                            @if($prestasi->penyelenggara) &bull; {{ $prestasi->penyelenggara }} @endif
                        </div>
                    @empty
                        <span class="text-muted italic">Belum ada prestasi non-akademik yang tercatat.</span>
                    @endforelse
                </td>
            </tr>
        </tbody>
    </table>

    @include('pdf.partials.rapor-ttd2')
</body>
</html>
