{{-- REMARK
 Tujuan       : Daftar master Satuan (Grid Master) beserta jumlah item pemakainya.
 Sumber hulu  : UnitController@index (units, items_count).
 Input        : rows.
 Output       : x-dl2::grid (DevLib 2); klik 2x / Edit membuka form satuan; saklar Aktif / Nonaktif.
 Dipakai oleh : route units.index (menu Master Data > Units, Supervisor).
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Units'])
@section('body')
    @include('partials.flash')
    {{-- Lebar dibatasi supaya kolom Unit, Conversions, dan jumlah item tidak terpisah jauh di layar lebar. --}}
    @php
        $tones = ['Hitungan' => 'info', 'Berat' => 'warning', 'Volume' => 'secondary', 'Panjang' => 'success', 'Luas' => 'dark', 'Lainnya' => 'dark'];
        $hasConv = collect($rows)->contains(fn ($r) => $r['conv'] !== '');   // kolom Konversi disembunyikan selama belum ada satu pun konversi
    @endphp
    <div style="max-width: 760px">
    <x-dl2::grid id="units" :config="[
        'rows' => $rows, 'actions' => 'master', 'activeKey' => 'active', 'newUrl' => route('units.create'), 'newLabel' => '+ Baru',
        'openUrl' => url('units/{id}'), 'toggleUrl' => url('units/{id}/toggle'),
        'labels' => ['search' => 'Cari kode atau nama...'],
        'phoneCards' => ['title' => 'name', 'sub' => 'code', 'badge' => 'kind', 'tones' => $tones, 'meta' => $hasConv ? ['conv', 'items'] : ['items']],
        'columns' => [
            ['key' => 'name', 'label' => 'Unit', 'type' => 'identity', 'code' => 'code', 'avatar' => false, 'box' => false],
            ['key' => 'kind', 'label' => 'Tipe', 'type' => 'badge', 'tones' => $tones],
            ...($hasConv ? [['key' => 'conv', 'label' => 'Konversi']] : []),
            ['key' => 'items', 'label' => 'Dipakai barang', 'type' => 'badge', 'tones' => ['0' => 'warning']],
        ],
        'stats' => [['label' => 'Semua satuan'], ['label' => 'Nonaktif', 'where' => ['active' => false], 'tone' => 'warn']],
    ]" />
    </div>
@endsection
