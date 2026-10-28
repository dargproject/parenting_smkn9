<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rapor Parenting - {{ $siswa->nama }}</title>
<style>
    @page { margin: 22mm 18mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #111; }
    h1 { text-align: center; font-size: 15px; margin: 0 0 2px; text-transform: uppercase; }
    h2 { text-align: center; font-size: 12px; margin: 0 0 16px; font-weight: normal; }
    h3 { font-size: 12px; margin: 16px 0 6px; text-transform: uppercase; border-bottom: 1px solid #000; padding-bottom: 2px; page-break-after: avoid; }
    table.isi tr { page-break-inside: avoid; }
    .blok-utuh { page-break-inside: avoid; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.header-bar td { border: none; padding: 0; vertical-align: middle; }
    table.header-bar td.logo-col { width: 65px; }
    table.header-bar img.logo { width: 55px; height: auto; }
    table.identitas td { padding: 2px 4px; vertical-align: top; }
    table.identitas td.label { width: 170px; font-weight: bold; }
    table.isi th, table.isi td { border: 1px solid #000; padding: 5px 6px; vertical-align: top; text-align: left; }
    table.isi th { background: #eee; font-size: 10.5px; }
    table.isi td.no { width: 22px; text-align: center; }
    table.isi td.aspek { width: 150px; font-weight: bold; }
    table.isi td.nilai { width: 110px; text-align: center; }
    .status-tuntas { color: #15803d; font-weight: bold; }
    .status-remedial { color: #b91c1c; font-weight: bold; }
    table.sub { width: 100%; border-collapse: collapse; margin: 0; }
    table.sub td { border: none; padding: 2px 4px; font-size: 10.5px; }
    table.sub td.sub-tanggal { width: 70px; }
    .status-aman { color: #15803d; font-weight: bold; }
    .status-sp { color: #b45309; font-weight: bold; }
    .status-berat { color: #b91c1c; font-weight: bold; }
    .catatan-blok { border: 1px solid #000; min-height: 60px; padding: 6px; font-size: 10.5px; margin-bottom: 10px; }
    .catatan-list { margin: 4px 0 0 14px; padding: 0; font-size: 9.5px; }
    .catatan-list li { margin-bottom: 2px; }
    .garis { border-bottom: 1px solid #000; height: 16px; margin-bottom: 4px; }
    .ttd { margin-top: 30px; text-align: right; font-size: 11px; }
    .kecil { font-size: 9.5px; color: #444; font-style: italic; }
</style>
</head>
<body>
    <table class="header-bar">
        <tr>
            <td class="logo-col">
                @if($logoPath)
                    <img src="{{ $logoPath }}" class="logo">
                @endif
            </td>
            <td>
                <h1>Rapor Parenting &amp; Perkembangan Peserta Didik</h1>
                <h1>SMK Negeri 9 Malang</h1>
            </td>
            <td class="logo-col"></td>
        </tr>
    </table>

    <h3>I. Identitas Siswa</h3>
    <table class="identitas">
        <tr><td class="label">Nama Siswa</td><td>: {{ $siswa->nama }}</td></tr>
        <tr><td class="label">Kelas / Program Keahlian</td><td>: {{ $kelas->nama_kelas }} / {{ $kelas->jurusan }}</td></tr>
        <tr><td class="label">Nama Wali Murid</td><td>: {{ $namaWali ?: '.......................................................' }}</td></tr>
        <tr><td class="label">Semester / Tahun Ajaran</td><td>: {{ $tahunAjaranAktif->nama ?? '-' }}</td></tr>
    </table>

    <h3>II. Capaian Akademik &amp; Keterampilan Kejuruan (Sekolah)</h3>
    <table class="isi">
        <tr>
            <th class="no">No</th>
            <th class="aspek">Aspek Penilaian</th>
            <th>Nilai &amp; Status</th>
        </tr>
        <tr>
            <td class="no">1</td>
            <td class="aspek">Progres Akademik &amp; Teori</td>
            <td style="padding: 4px;">
                @forelse($akademikPerMapel as $a)
                    <table class="sub">
                        <tr>
                            <td>
                                <strong>{{ $a['nama'] }}</strong><br>
                                <span class="status-remedial">Perlu peningkatan @if(! empty($a['materi'])) pada materi: {{ implode(', ', $a['materi']) }}@endif.</span>
                            </td>
                        </tr>
                    </table>
                @empty
                    <span class="kecil">Tidak ada mata pelajaran yang memerlukan peningkatan saat ini.</span>
                @endforelse
            </td>
        </tr>
        {{-- <tr>
            <td class="no">2</td>
            <td class="aspek">Praktik Keahlian (Product/Service)</td>
            <td>
                @if($rataPraktik !== null)
                    {{ number_format($rataPraktik, 1) }} &mdash;
                    <span class="status-{{ $statusPraktik === 'tuntas' ? 'tuntas' : 'remedial' }}">{{ ucfirst($statusPraktik) }}</span>
                @else
                    <span class="kecil">Belum ada nilai PKL/UKK</span>
                @endif
            </td>
        </tr> --}}
        <tr>
            <td class="no">3</td>
            <td class="aspek">Kedisiplinan &amp; Kehadiran</td>
            <td>
                @if($totalPresensi > 0)
                    {{ $persenHadir }}% hadir dari {{ $totalPresensi }} pertemuan &mdash;
                    Sakit: {{ $rekapKehadiran['S'] }}, Izin: {{ $rekapKehadiran['I'] }},
                    <span class="status-remedial">Alpa (tanpa keterangan): {{ $rekapKehadiran['A'] }}</span>.<br>
                @else
                    <span class="kecil">Belum ada data presensi semester ini.</span><br>
                @endif
                <span class="status-{{ $statusSanksi['tone'] === 'emerald' ? 'aman' : ($statusSanksi['tone'] === 'amber' ? 'sp' : 'berat') }}">{{ $statusSanksi['label'] }}</span>
                @if($ketidakhadiranPerMapel->isNotEmpty())
                    <br><strong>Tidak Hadir per Mata Pelajaran:</strong>
                    <ul class="catatan-list">
                        @foreach($ketidakhadiranPerMapel as $m)
                            <li>{{ $m['nama'] }}:
                                @if($m['alpa'] > 0) <span class="status-remedial">Alpa {{ $m['alpa'] }}</span> @endif
                                @if($m['sakit'] > 0) Sakit {{ $m['sakit'] }} @endif
                                @if($m['izin'] > 0) Izin {{ $m['izin'] }} @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if($pelanggaranList->isNotEmpty())
                    <br><strong>Catatan Kedisiplinan:</strong>
                    <ul class="catatan-list">
                        @foreach($pelanggaranList as $p)
                            <li>{{ \Carbon\Carbon::parse($p->tanggal)->format('d-m-Y') }} &mdash; {{ $p->judul }} ({{ $p->poin }} poin)@if($p->tindakan), {{ $p->tindakan }}@endif</li>
                        @endforeach
                    </ul>
                @endif
            </td>
        </tr>
    </table>

    <h3>III. Perkembangan Non-Akademik &amp; Karakter (Sekolah &amp; Ekstrakurikuler)</h3>
    <table class="isi">
        <tr>
            <th class="no">No</th>
            <th class="aspek">Aspek Karakter &amp; Kegiatan</th>
            <th>Catatan Perkembangan</th>
        </tr>
        <tr>
            <td class="no">1</td>
            <td class="aspek">Kegiatan Ekstrakurikuler / Organisasi</td>
            <td>
                @forelse($nilaiEkskul as $ne)
                    {{ $ne->ekstrakurikuler->nama ?? '-' }} &mdash; Nilai {{ $ne->nilai }}@if($ne->catatan) ({{ $ne->catatan }})@endif<br>
                @empty
                    <span class="kecil">Tidak mengikuti ekstrakurikuler yang tercatat.</span>
                @endforelse
            </td>
        </tr>
        <tr>
            <td class="no">2</td>
            <td class="aspek">Soft Skills, Komunikasi &amp; Kreativitas / Inisiatif</td>
            <td>{{ $catatanKarakter ?: '-' }}</td>
        </tr>
        <tr>
            <td class="no">3</td>
            <td class="aspek">Bakat &amp; Minat (Tes Peminatan BK)</td>
            <td>
                @if($peminatan)
                    {{ collect([$peminatan->pilihan1, $peminatan->pilihan2, $peminatan->pilihan3])->filter()->implode(', ') }}
                    @if($peminatan->catatan)
                        <br><span class="kecil">{{ $peminatan->catatan }}</span>
                    @endif
                @else
                    <span class="kecil">Belum ada data Tes Peminatan BK yang ditampilkan ke orang tua.</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="no">4</td>
            <td class="aspek">Prestasi Non-Akademik</td>
            <td>
                @forelse($prestasiNonAkademik as $prestasi)
                    {{ $prestasi->nama_prestasi }} ({{ $prestasi->tingkat }}) &mdash; {{ $prestasi->tanggal->translatedFormat('d M Y') }}
                    @if($prestasi->peringkat) &middot; {{ $prestasi->peringkat }} @endif
                    @if($prestasi->penyelenggara) &middot; {{ $prestasi->penyelenggara }} @endif
                    <br>
                @empty
                    <span class="kecil">Belum ada prestasi non-akademik yang tercatat.</span>
                @endforelse
            </td>
        </tr>
    </table>

    <div class="blok-utuh">
        <h3>IV. Lembar Pantauan Parenting (diisi oleh Wali Murid)</h3>
        <p class="kecil">Bapak/Ibu Wali Murid dimohon memberikan checklist (&radic;) dan catatan ringkas kondisi anak di rumah:</p>
        <table class="isi">
            <tr>
                <th>No</th>
                <th style="width: 180px;">Kebiasaan / Perilaku di Rumah</th>
                <th style="width: 50px;">Selalu</th>
                <th style="width: 50px;">Kadang</th>
                <th style="width: 50px;">Belum</th>
                <th>Catatan Orang Tua</th>
            </tr>
            @foreach(['Kedisiplinan belajar & latihan mandiri', 'Pembatasan & pemanfaatan gadget / media sosial', 'Komunikasi terbuka dengan keluarga', 'Keterlibatan dalam tugas/pekerjaan rumah'] as $i => $label)
                <tr>
                    <td class="no">{{ $i + 1 }}</td>
                    <td>{{ $label }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td style="height: 26px;">&nbsp;</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="blok-utuh">
        <h3>V. Catatan Kolaborasi &amp; Rencana Tindak Lanjut</h3>
        <p><strong>Tanggapan / Harapan Orang Tua:</strong></p>
        <div class="catatan-blok">&nbsp;</div>
    </div>

    <p class="ttd">Malang, {{ now()->translatedFormat('d F Y') }}</p>
</body>
</html>
