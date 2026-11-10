<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Rapor Parenting - {{ $siswa->nama }}</title>
@include('pdf.partials.rapor-style')
</head>
<body>
    @include('pdf.partials.rapor-header')

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
            <td class="no">2</td>
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

    {{-- <div class="blok-utuh">
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
    </div> --}}

    {{-- <div class="blok-utuh">
        <h3>V. Catatan Kolaborasi &amp; Rencana Tindak Lanjut</h3>
        <p><strong>Tanggapan / Harapan Orang Tua:</strong></p>
        <div class="catatan-blok">&nbsp;</div>
    </div> --}}

    @include('pdf.partials.rapor-ttd')
6<2/body>
</html>
