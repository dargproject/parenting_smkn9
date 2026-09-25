@include('admin.partials.entity-form', ['title'=>'Edit Jenis Pelanggaran','action'=>route('admin.master-pelanggaran.update',$item),'method'=>'PUT','back'=>route('admin.master-pelanggaran.index'),'record'=>$item,'fields'=>[
    ['name'=>'nama_pelanggaran','label'=>'Nama Pelanggaran','required'=>true,'wide'=>true],
    ['name'=>'pasal_id','label'=>'Pasal','type'=>'select','options'=>$pasals->pluck('nama','id'),'required'=>true],
    ['name'=>'jenis_id','label'=>'Jenis (menentukan poin otomatis)','type'=>'select','options'=>$jenisList->mapWithKeys(fn($j) => [$j->id => $j->nama.' — '.$j->poin.' poin']),'required'=>true],
    ['name'=>'is_active','label'=>'Status Aktif','type'=>'checkbox'],
]])
