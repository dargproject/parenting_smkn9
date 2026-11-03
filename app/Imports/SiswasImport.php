<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswasImport implements ToModel, WithHeadingRow
{
    public int $berhasil = 0;

    public int $diperbarui = 0;

    public int $dilewati = 0;

    private string $passwordHash;

    public function __construct()
    {
        // Di-hash sekali di sini, bukan per baris: bcrypt sengaja lambat (~100-200ms),
        // ratusan baris x Hash::make() per baris bisa gampang melebihi batas waktu eksekusi PHP.
        $this->passwordHash = Hash::make('password');
    }

    public function model(array $row): ?Siswa
    {
        if (empty($row['nis'])) {
            return null;
        }

        $nisn = $row['nisn'] ?? null;
        $nipd = $row['nipd'] ?? null;

        $siswa = Siswa::where('nis', $row['nis'])->first()
            ?? ($nisn ? Siswa::where('nisn', $nisn)->first() : null)
            ?? ($nipd ? Siswa::where('nipd', $nipd)->first() : null);

        $namaKelas = ! empty($row['kelas']) ? trim((string) $row['kelas']) : null;
        $kelas = $namaKelas ? Kelas::where('nama_kelas', $namaKelas)->first() : null;

        // Siswa sudah ada (dicocokkan lewat NIS/NISN/NIPD): perbarui kolom yang diisi di file,
        // bukan dilewati total -- supaya upload ulang untuk melengkapi data yang tadinya kosong
        // (mis. No. HP Ortu) bisa langsung masuk tanpa harus hapus data lama dulu. Kolom yang
        // dikosongkan di file dibiarkan memakai nilai lama, tidak ikut ditimpa jadi kosong.
        if ($siswa) {
            if ($namaKelas && ! $kelas) {
                $this->dilewati++;

                return null;
            }

            $siswa->fill(array_filter([
                'nisn' => $nisn, 'nipd' => $nipd,
                'nama' => $row['nama'] ?? null,
                'kelas_id' => $kelas?->id,
                'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
                'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
            ], fn ($nilai) => $nilai !== null && $nilai !== ''));

            $this->diperbarui++;

            return $siswa;
        }

        if (! $kelas) {
            $this->dilewati++;

            return null;
        }

        $this->berhasil++;

        return new Siswa([
            'nis' => $row['nis'], 'nisn' => $nisn, 'nipd' => $nipd,
            'nama' => $row['nama'] ?? '', 'kelas_id' => $kelas->id,
            'password' => $this->passwordHash, 'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
            'tanggal_lahir' => $row['tanggal_lahir'] ?? null, 'no_hp_ortu' => $row['no_hp_ortu'] ?? null,
        ]);
    }
}
