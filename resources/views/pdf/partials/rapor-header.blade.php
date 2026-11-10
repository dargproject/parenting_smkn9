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
