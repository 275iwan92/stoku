{{-- REMARK
 Tujuan       : Form master Kategori (Single Form kind=master): awalan kode barang 3 karakter (tetap setelah disimpan) dan nama, dengan contoh kode barang yang dihasilkan.
 Sumber hulu  : CategoryController (category).
 Input        : category.
 Output       : POST categories.store (baru) / PUT categories.update (ubah nama, Activate, Deactivate).
 Dipakai oleh : routes categories.create, categories.show.
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Category'])
@section('body')
    @php
        $isNew = ! $category->exists;
        $v = fn (string $k) => old($k, $category->{$k});
        $e = fn (string $k) => $errors->first($k) ?: null;
        $p = $category->code ?: 'XXX';
    @endphp
    @include('partials.flash')
    <x-dl2::master-form :title="$isNew ? 'Kategori · Baru' : 'Kategori · '.raw($category->code)" :subtitle="$isNew ? null : raw($category->name)"
        :status="$isNew ? 'new' : ($category->is_active ? 'active' : 'inactive')" :back="route('categories.index')" :new-url="route('categories.create')"
        :action="$isNew ? route('categories.store') : route('categories.update', $category)" :method="$isNew ? 'post' : 'put'"
        :created-at="$category->created_at?->format('Y-m-d H:i:s')" :updated-at="$category->updated_at?->format('Y-m-d H:i:s')">
        <x-dl2::form-row>
            <div class="dl2-col-12 dl2-col-md-4"><x-dl2::field.text name="code" label="Code Prefix" :value="$v('code')" code :case-toggle="false" required :readonly="! $isNew" maxlength="3" :error="$e('code')" hint="Exactly 3 letters or digits. Cannot be changed after saving." /></div>
            <div class="dl2-col-12 dl2-col-md-4"><x-dl2::field.text name="name" label="Category Name" :value="$v('name')" required maxlength="100" :error="$e('name')" /></div>
            @unless ($isNew)
                <div class="dl2-col-12 dl2-col-md-4"><x-dl2::display :config="['label' => 'Items using it', 'value' => (string) $category->products_count]" /></div>
            @endunless
        </x-dl2::form-row>
        <p class="dl-note">Rumus kode barang: <b>{{ $p }}-0001</b> (urutan berjalan per kategori, tanpa tanggal). Barang berkedaluwarsa punya satu batch per tanggal dengan kode <b>{{ $p }}-0001-260315</b> (kedaluwarsa 15 Maret 2026).</p>
    </x-dl2::master-form>
@endsection
