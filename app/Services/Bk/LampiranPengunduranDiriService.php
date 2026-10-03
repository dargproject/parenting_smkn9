<?php

namespace App\Services\Bk;

use App\Models\LampiranPengunduranDiri;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class LampiranPengunduranDiriService
{
    /**
     * @param  UploadedFile[]  $files
     */
    public function storeLampirans(int $pengunduranDiriId, array $files): void
    {
        foreach ($files as $file) {
            $path = $file->store('bk/lampiran/pengunduran-diri', 'public');

            LampiranPengunduranDiri::create([
                'pengunduran_diri_id' => $pengunduranDiriId,
                'nama_file' => $file->getClientOriginalName(),
                'path_file' => $path,
                'tipe_file' => $file->getClientOriginalExtension(),
                'ukuran' => $file->getSize(),
            ]);
        }
    }

    public function deleteLampiran(int $id): void
    {
        $lampiran = LampiranPengunduranDiri::find($id);
        if ($lampiran) {
            Storage::disk('public')->delete($lampiran->path_file);
            $lampiran->delete();
        }
    }
}
