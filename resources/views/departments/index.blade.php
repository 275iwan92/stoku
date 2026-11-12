{{-- REMARK
 Tujuan       : Daftar master Departemen (Grid Master): kode + nama, jumlah penerima dan dokumen pengeluaran.
 Sumber hulu  : DepartmentController@index.
 Input        : rows.
 Output       : x-dl2::grid (DevLib 2); klik 2x / Edit membuka form departemen.
 Dipakai oleh : route departments.index (menu Data Master > Departemen).
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Departments'])
@section('body')
    @php $rows = collect($rows)->map(fn ($r) => $r + ['recipientsTone' => $r['recipients'] ? 'neutral' : 'warn', 'documentsTone' => $r['documents'] ? 'neutral' : 'warn'])->all(); @endphp
    @include('partials.flash')
    <x-dl2::grid id="departments" :config="[
        'rows' => $rows, 'actions' => 'master', 'activeKey' => 'active', 'newUrl' => route('departments.create'), 'newLabel' => '+ Baru',
        'openUrl' => url('departments/{id}'), 'toggleUrl' => url('departments/{id}/toggle'),
        'labels' => ['search' => 'Search code, name...'],
        'phoneCards' => ['title' => 'name', 'sub' => 'code', 'meta' => ['recipients', 'documents']],
        'columns' => [
            ['key' => 'name', 'label' => 'Department', 'type' => 'identity', 'code' => 'code', 'avatar' => false, 'box' => false],
            ['key' => 'recipients', 'label' => 'Recipients', 'type' => 'badge', 'tones' => ['0' => 'warning']],
            ['key' => 'documents', 'label' => 'Issuance Documents', 'type' => 'badge', 'tones' => ['0' => 'warning']],
        ],
        'stats' => [['label' => 'All departments'], ['label' => 'Inactive', 'where' => ['active' => false], 'tone' => 'warn']],
    ]" />
@endsection
