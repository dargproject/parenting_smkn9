<table class="header-table">
    <tr>
        <td class="logo-container">
            @if(isset($logoPath) && $logoPath)
                <img src="{{ $logoPath }}" class="logo">
            @endif
        </td>
        <td class="title-container">
            <h1>Rapor Parenting &amp; Perkembangan Peserta Didik</h1>
            <h2>SMK Negeri 9 Malang</h2>
        </td>
        <td class="logo-container">
            {{-- Spacer penyeimbang alignment --}}
        </td>
    </tr>
</table>

<div class="section-title">I. Identitas Siswa</div>
<table class="table-identitas">
    <tr>
        <td class="label">Nama Siswa</td>
        <td class="colon">:</td>
        <td class="value"><strong>{{ $siswa->nama }}</strong></td>
    </tr>
    <tr>
        <td class="label">Kelas / Jurusan</td>
        <td class="colon">:</td>
        <td class="value">{{ $kelas->nama_kelas }} / {{ $kelas->jurusan }}</td>
    </tr>
    <tr>
        <td class="label">Nama Wali Murid</td>
        <td class="colon">:</td>
        <td class="value">{{ $namaWali ?: '.......................................................' }}</td>
    </tr>
    <tr>
        <td class="label">Semester / Tahun Ajaran</td>
        <td class="colon">:</td>
        <td class="value">{{ $tahunAjaranAktif->nama ?? '-' }}</td>
    </tr>
</table>
