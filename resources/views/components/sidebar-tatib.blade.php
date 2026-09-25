<div x-data="{ openTatib: true }">
    <button type="button" @click="openTatib = !openTatib" class="flex items-center w-full px-4 py-3 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors">
        <i class="fa-solid fa-gavel w-6 text-center mr-2"></i> <span>Tatib</span>
        <i class="fa-solid fa-chevron-down ml-auto text-xs transition-transform" :class="{ 'rotate-180': openTatib }"></i>
    </button>
    <div x-show="openTatib">
        <a href="#" class="flex items-center pl-12 pr-4 py-2.5 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors text-sm" onclick="showPane('pane-kesiswaan-jenis', this)"><span>Jenis Pelanggaran</span></a>
        <a href="#" class="flex items-center pl-12 pr-4 py-2.5 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors text-sm" onclick="showPane('pane-kesiswaan-catat', this)"><span>Catat Pelanggaran</span></a>
        <a href="#" class="flex items-center pl-12 pr-4 py-2.5 text-slate-400 hover:text-slate-100 hover:bg-slate-800/50 transition-colors text-sm" onclick="showPane('pane-kesiswaan-rekap', this)"><span>Rekap Poin</span></a>
    </div>
</div>
