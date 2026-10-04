@include('admin.partials.entity-form', ['title'=>'Tambah Ekstrakurikuler','action'=>route('admin.ekstrakurikuler.store'),'method'=>'POST','back'=>route('admin.ekstrakurikuler.index'),'record'=>$item,'fields'=>[
    ['name'=>'nama_ekskul','label'=>'Nama Ekstrakurikuler','required'=>true,'wide'=>true],
    ['name'=>'guru_id','label'=>'Pembina','type'=>'select','options'=>$pembinaOptions],
    ['name'=>'is_active','label'=>'Status Aktif','type'=>'checkbox'],
]])
