@extends('layouts.app')

@section('content')
<div class="admin-dashboard mx-auto max-w-4xl space-y-6">
    <div><h1 class="text-2xl font-bold">{{ $title }}</h1></div>
    @include('admin.partials.flash')
    <form method="POST" action="{{ $action }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700/60 dark:bg-slate-800/80 md:p-6">
        @csrf
        @if($method !== 'POST') @method($method) @endif
        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            @foreach($fields as $field)
                <div class="{{ ($field['wide'] ?? false) ? 'md:col-span-2' : '' }}">
                    <label for="{{ $field['name'] }}" class="mb-1.5 block text-sm font-medium">{{ $field['label'] }}</label>
                    @if(($field['type'] ?? 'text') === 'select')
                        <select id="{{ $field['name'] }}" name="{{ $field['name'] }}" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                            @foreach($field['options'] as $value => $label)<option value="{{ $value }}" @selected(old($field['name'], data_get($record, $field['name'])) == $value)>{{ $label }}</option>@endforeach
                        </select>
                    @elseif(($field['type'] ?? 'text') === 'checkbox')
                        <label class="flex items-center gap-2 py-2"><input type="checkbox" name="{{ $field['name'] }}" value="1" @checked(old($field['name'], data_get($record, $field['name'])))> {{ $field['label'] }}</label>
                    @elseif(($field['type'] ?? 'text') === 'textarea')
                        <textarea id="{{ $field['name'] }}" name="{{ $field['name'] }}" rows="3" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900">{{ old($field['name'], data_get($record, $field['name'])) }}</textarea>
                    @else
                        <input id="{{ $field['name'] }}" name="{{ $field['name'] }}" type="{{ $field['type'] ?? 'text' }}" value="{{ old($field['name'], data_get($record, $field['name'])) }}" class="w-full rounded-lg border border-slate-300 bg-transparent px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-900" {{ ($field['required'] ?? false) ? 'required' : '' }}>
                    @endif
                    @error($field['name'])<p class="mt-1 text-xs text-rose-500">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
        <div class="mt-6 flex justify-end gap-3"><a href="{{ $back }}" class="rounded-lg border border-slate-300 px-5 py-2.5 text-sm dark:border-slate-600">Batal</a><button class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-500">Simpan</button></div>
    </form>
</div>
@endsection
