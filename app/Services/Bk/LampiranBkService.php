<?php

namespace App\Services\Bk;

use App\Models\LampiranKasusBk;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LampiranBkService
{
    /**
     * Simpan file lampiran dan buat baris di tabel lampiran_kasus_bks.
     *
     * @param  UploadedFile[]  $files
     */
    public function storeLampirans(int $kasusBkId, array $files, string $subFolder = 'lampiran'): void
    {
        foreach ($files as $file) {
            $path = $file->store("bk/lampiran/{$subFolder}", 'public');

            LampiranKasusBk::create([
                'kasus_bk_id' => $kasusBkId,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'tipe_file' => $file->getClientOriginalExtension(),
                'ukuran' => $file->getSize(),
            ]);
        }
    }

    public function deleteLampiran(int $id): void
    {
        $lampiran = LampiranKasusBk::find($id);
        if ($lampiran) {
            Storage::disk('public')->delete($lampiran->path_file);
            $lampiran->delete();
        }
    }

    public function deleteMultiple(array $ids): void
    {
        foreach ($ids as $id) {
            $this->deleteLampiran($id);
        }
    }
}
