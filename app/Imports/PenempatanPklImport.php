<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\PenempatanPkl;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PenempatanPklImport implements ToModel, WithHeadingRow
{
    public int $berhasil = 0;

    public int $diperbarui = 0;

    public array $errors = [];

    private ?int $tahunAjaranAktifId;

    public function __construct()
    {
        $this->tahunAjaranAktifId = TahunAjaran::where('is_active', true)->value('id');
    }

    public function model(array $row): ?PenempatanPkl
    {
        $namaSiswa = trim((string) ($row['nama_siswa'] ?? ''));
        $namaKelas = trim((string) ($row['kelas'] ?? ''));
        $namaIduka = trim((string) ($row['nama_iduka'] ?? ''));

        if (empty($namaSiswa) || empty($namaIduka)) {
            return null;
        }

        if (! $this->tahunAjaranAktifId) {
            $this->errors[] = "{$namaSiswa}: tidak ada tahun ajaran aktif.";

            return null;
        }

        $siswa = Siswa::where('nama', $namaSiswa)
            ->when($namaKelas, fn ($q) => $q->whereHas('kelas', fn ($qk) => $qk->where('nama_kelas', $namaKelas)))
            ->first();

        if (! $siswa) {
            $this->errors[] = "{$namaSiswa} ({$namaKelas}): siswa tidak ditemukan -- nama & kelas harus persis sama dengan data Siswa.";

            return null;
        }

        $namaPembimbing = trim((string) ($row['pembimbing_sekolah'] ?? ''));
        $guruPembimbingId = null;
        if ($namaPembimbing) {
            $guruPembimbingId = Guru::whereHas('roles', fn ($q) => $q->where('name', 'pembimbing_pkl'))
                ->where('nama', $namaPembimbing)->value('id');

            if (! $guruPembimbingId) {
                $this->errors[] = "{$namaSiswa}: pembimbing \"{$namaPembimbing}\" tidak ditemukan/belum berrole Pembimbing PKL -- dikosongkan, bisa dipilih manual nanti.";
            }
        }

        $data = [
            'siswa_id' => $siswa->id,
            'tahun_ajaran_id' => $this->tahunAjaranAktifId,
            'nama_iduka' => $namaIduka,
            'alamat' => $row['alamat'] ?? null,
            'nama_cp_industri' => $row['nama_cp_industri'] ?? null,
            'no_cp_industri' => $row['cp_industri'] ?? null,
            'guru_pembimbing_id' => $guruPembimbingId,
        ];

        $existing = PenempatanPkl::where('siswa_id', $siswa->id)->where('tahun_ajaran_id', $this->tahunAjaranAktifId)->first();

        if ($existing) {
            $existing->fill($data);
            $this->diperbarui++;

            return $existing;
        }

        $this->berhasil++;

        return new PenempatanPkl($data);
    }
}
