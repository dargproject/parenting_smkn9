@props(['name' => 'foto', 'label' => 'Foto (opsional)'])
<div x-data="fotoUploader()">
    <label class="form-label small font-semibold">{{ $label }}</label>
    <div class="flex gap-2">
        <button type="button" @click="$refs.fotoGaleri.click()" class="btn btn-sm btn-outline-secondary text-slate-100 flex-1">
            <i class="fa-solid fa-image"></i> Pilih File
        </button>
        <button type="button" @click="bukaKamera()" class="btn btn-sm btn-outline-secondary text-slate-100 flex-1">
            <i class="fa-solid fa-camera"></i> Kamera
        </button>
    </div>
    <input type="file" name="{{ $name }}" x-ref="fotoFinal" accept="image/*" class="hidden">
    <input type="file" x-ref="fotoGaleri" accept="image/*" class="hidden" @change="kompresFoto($event, $refs.fotoFinal)">
    <p class="text-xs text-slate-400 mt-1" x-show="fotoStatus" x-text="fotoStatus" style="display: none;"></p>
    @error($name)
        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
    @enderror

    <div
        x-show="kameraAktif"
        style="display: none;"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4 bg-black/70"
        @keydown.escape.window="tutupKamera()"
    >
        <div class="solid-panel w-full max-w-md rounded-2xl p-4 text-slate-100 shadow-xl">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-base font-semibold">Ambil Foto</h3>
                <button type="button" @click="tutupKamera()" class="text-slate-400 hover:text-slate-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <p class="text-xs text-rose-400 mb-2" x-show="kameraError" x-text="kameraError" style="display: none;"></p>

            <video x-ref="videoKamera" autoplay playsinline muted class="w-full rounded-lg bg-black" x-show="!kameraError" style="display: none;"></video>

            <div class="mt-3 flex gap-2">
                <button type="button" @click="ambilFotoDariKamera()" x-show="!kameraError" style="display: none;" class="btn btn-sm btn-primary flex-1">
                    <i class="fa-solid fa-camera"></i> Jepret
                </button>
                <button type="button" @click="tutupKamera()" class="btn btn-sm btn-outline-secondary text-slate-100 flex-1">
                    Batal
                </button>
            </div>
        </div>
    </div>
</div>
