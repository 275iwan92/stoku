{{-- REMARK
 Tujuan       : Form master Lokasi Penyimpanan (Single Form kind=master): kode (tetap setelah disimpan) dan nama.
 Sumber hulu  : LocationController (location).
 Input        : location.
 Output       : POST locations.store (baru) / PUT locations.update (ubah nama, Activate, Deactivate).
 Dipakai oleh : routes locations.create, locations.show.
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Storage Location'])
@section('body')
    @php
        $isNew = ! $location->exists;
        $v = fn (string $k) => old($k, $location->{$k});
        $e = fn (string $k) => $errors->first($k) ?: null;
    @endphp
    @include('partials.flash')
    <x-dl2::master-form :title="$isNew ? 'Lokasi · Baru' : 'Lokasi · '.raw($location->code)" :subtitle="$isNew ? null : raw($location->name)"
        :status="$isNew ? 'new' : ($location->is_active ? 'active' : 'inactive')" :back="route('locations.index')" :new-url="route('locations.create')"
        :action="$isNew ? route('locations.store') : route('locations.update', $location)" :method="$isNew ? 'post' : 'put'"
        :created-at="$location->created_at?->format('Y-m-d H:i:s')" :updated-at="$location->updated_at?->format('Y-m-d H:i:s')">
        <x-dl2::form-row>
            <div class="dl2-col-12 dl2-col-md-4"><x-dl2::field.text name="code" label="Location Code" :value="$v('code')" code :case-toggle="false" required :readonly="! $isNew" maxlength="50" :error="$e('code')" hint="Unique, for example A1-01. Cannot be changed after saving." /></div>
            <div class="dl2-col-12 dl2-col-md-4"><x-dl2::field.text name="name" label="Location Name" :value="$v('name')" required maxlength="100" :error="$e('name')" /></div>
            <div class="dl2-col-12 dl2-col-md-4"><x-dl2::field type="select" name="type" label="Location Type" :options="['' => 'Belum ditentukan'] + \App\Models\Location::TYPES" :value="(string) $v('type')" help="Groups the location in the list (cabinet, closed warehouse, open warehouse, wide area)." /></div>
        </x-dl2::form-row>
        @unless ($isNew)
            <x-dl2::form-row>
                <div class="dl2-col-12 dl2-col-md-4"><x-dl2::display :config="['label' => 'Items using it', 'value' => (string) $location->products_count]" /></div>
            </x-dl2::form-row>
        @endunless
    </x-dl2::master-form>
@endsection
