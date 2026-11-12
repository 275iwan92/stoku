{{-- REMARK
 Tujuan       : Daftar master Penerima barang (Grid Master): nama + departemen bawaan, jumlah dokumen pengeluaran.
 Sumber hulu  : RecipientController@index.
 Input        : rows.
 Output       : x-dl2::grid (DevLib 2); klik 2x / Edit membuka form penerima.
 Dipakai oleh : route recipients.index (menu Data Master > Penerima).
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Recipients'])
@section('body')
    @include('partials.flash')
    <x-dl2::grid id="recipients" :config="[
        'rows' => $rows, 'actions' => 'master', 'activeKey' => 'active', 'newUrl' => route('recipients.create'), 'newLabel' => '+ Baru',
        'openUrl' => url('recipients/{id}'), 'toggleUrl' => url('recipients/{id}/toggle'),
        'labels' => ['search' => 'Search name, department...'],
        'phoneCards' => ['title' => 'name', 'sub' => 'department', 'meta' => ['documents']],
        'columns' => [
            ['key' => 'name', 'label' => 'Recipient', 'type' => 'identity', 'code' => 'department', 'box' => false],
            ['key' => 'documents', 'label' => 'Issuance Documents', 'type' => 'number'],
        ],
        'stats' => [['label' => 'All recipients'], ['label' => 'Inactive', 'where' => ['active' => false], 'tone' => 'warn']],
    ]" />
@endsection
