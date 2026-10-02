<?php

namespace App\Services\Bk;

use App\Models\KasusBk;
use InvalidArgumentException;
use RuntimeException;
use ZipArchive;

class WordExportService
{
    private const VALID_TEMPLATES = [
        'form-penanganan-siswa',
        'komulatif-record',
        'lembar-sosiometri',
    ];

    public function generateDocument(KasusBk $kasusBk, string $templateName): string
    {
        $this->validateTemplate($templateName);
        $templatePath = $this->getTemplatePath($templateName);
        $this->ensureTemplateExists($templatePath);

        $data = $this->buildTemplateData($kasusBk);

        $tempPath = $this->createTempCopy($templatePath, $kasusBk->id);
        $this->injectDataToXml($tempPath, $templateName, $data);

        return $tempPath;
    }

    private function buildTemplateData(KasusBk $kasusBk): array
    {
        $siswa = $kasusBk->siswa;
        $tanggalMulai = $kasusBk->tanggal_mulai;

        return [
            'nama_siswa' => $siswa->nama ?? '-',
            'nis' => (string) ($siswa->nis ?? '-'),
            'kelas' => $siswa->kelas->nama_kelas ?? '-',
            'jurusan' => $siswa->kelas->jurusan ?? '-',
            'alamat_siswa' => $siswa->profilSiswa->alamat ?? '-',
            'jenis_kelamin' => $siswa->jenis_kelamin ?? '-',
            'judul' => $kasusBk->judul ?? '-',
            'prioritas' => $kasusBk->prioritas ?? '-',
            'tanggal_mulai' => $tanggalMulai ? $tanggalMulai->locale('id')->translatedFormat('l, d F Y') : '-',
            'tanggal_hari' => $tanggalMulai ? $tanggalMulai->locale('id')->translatedFormat('l') : '-',
            'tanggal_tgl' => $tanggalMulai ? $tanggalMulai->format('d/m/Y') : '-',
            'deskripsi' => $kasusBk->deskripsi ?? '-',
            'hasil_akhir' => $kasusBk->tindak_lanjut ?? '-',
            'status' => $kasusBk->status ?? '-',
            'nama_konselor' => $kasusBk->konselor->nama ?? '-',
            'nip_konselor' => $kasusBk->konselor->nip ?? '-',
            'nama_kategori' => $kasusBk->kategoriKasus->nama_kategori ?? ($kasusBk->kategori ?? '-'),
            'tahun_ajaran' => $kasusBk->tahunAjaran->nama ?? '-',
            'tanggal_cetak' => now()->locale('id')->translatedFormat('d F Y'),
        ];
    }

    private function injectDataToXml(string $tempPath, string $templateName, array $data): void
    {
        $fieldMap = $this->getFieldMap($templateName);
        $zip = new ZipArchive;
        if ($zip->open($tempPath) !== true) {
            throw new RuntimeException('Gagal membuka file template temporary.');
        }
        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('Gagal membaca document.xml dari template.');
        }
        foreach ($fieldMap as $label => $variable) {
            if (! isset($data[$variable])) {
                continue;
            }
            $value = htmlspecialchars($data[$variable], ENT_XML1, 'UTF-8');
            $escapedLabel = preg_quote($label, '/');
            $labelPattern = '/<w:t(?:\s[^>]*)?>'.$escapedLabel.'<\/w:t>/';
            if (! preg_match($labelPattern, $xml, $m, PREG_OFFSET_CAPTURE)) {
                continue;
            }
            $labelEnd = $m[0][1] + strlen($m[0][0]);
            $afterLabel = substr($xml, $labelEnd);
            if (preg_match('/<w:t(?:\s[^>]*)?>([^<]*)<\/w:t>/', $afterLabel, $tm, PREG_OFFSET_CAPTURE)) {
                $matchStart = $labelEnd + $tm[0][1];
                $matchLen = strlen($tm[0][0]);
                $oldContent = $tm[1][0];
                $newContent = str_contains($oldContent, ':') ? ': '.$value : $value;
                if (preg_match('/<w:t([^>]*)>/', $tm[0][0], $attrMatch)) {
                    $newTag = '<w:t'.$attrMatch[1].'>'.$newContent.'</w:t>';
                    $xml = substr($xml, 0, $matchStart).$newTag.substr($xml, $matchStart + $matchLen);
                }
            }
        }
        $zip->deleteName('word/document.xml');
        $zip->addFromString('word/document.xml', $xml);
        $zip->close();
    }

    /**
     * Pemetaan label (teks persis di dalam template .docx) -> kunci data. Hanya label yang diikuti
     * SATU baris blanko tersendiri yang dipetakan di sini -- beberapa label di template (mis. "No. Induk"
     * pada komulatif-record, "NIP." pada form-penanganan-siswa) berbagi satu baris Word yang sama dengan
     * label lain atau tidak diikuti baris blanko sama sekali, sehingga sengaja tidak diisi otomatis agar
     * tidak merusak tata letak baris tersebut.
     */
    private function getFieldMap(string $templateName): array
    {
        return match ($templateName) {
            'form-penanganan-siswa' => [
                'NAMA' => 'nama_siswa', 'KELAS' => 'kelas',
                'TAHUN AJARAN' => 'tahun_ajaran', 'ALAMAT' => 'alamat_siswa',
            ],
            'komulatif-record' => [
                'Nama Siswa' => 'nama_siswa', 'Alamat Rumah ' => 'alamat_siswa',
            ],
            'lembar-sosiometri' => [
                'Nama' => 'nama_siswa', 'Kelas ' => 'kelas',
            ],
            default => [],
        };
    }

    private function validateTemplate(string $templateName): void
    {
        if (! in_array($templateName, self::VALID_TEMPLATES, true)) {
            throw new InvalidArgumentException("Template '{$templateName}' tidak valid.");
        }
    }

    private function getTemplatePath(string $templateName): string
    {
        return resource_path("templates/{$templateName}.docx");
    }

    private function ensureTemplateExists(string $path): void
    {
        if (! file_exists($path)) {
            throw new RuntimeException("File template tidak ditemukan: {$path}");
        }
    }

    private function createTempCopy(string $templatePath, int $kasusId): string
    {
        $tempDir = sys_get_temp_dir();
        $filename = "kasus-bk-{$kasusId}-".time().'-'.bin2hex(random_bytes(4)).'.docx';
        $tempPath = $tempDir.DIRECTORY_SEPARATOR.$filename;
        if (! copy($templatePath, $tempPath)) {
            throw new RuntimeException('Gagal membuat salinan template.');
        }

        return $tempPath;
    }
}
