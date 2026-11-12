{{-- REMARK
 Tujuan       : Formulir aksi satu baris (POST / GET / PUT) berisi tombol dan isian dari slot, dengan token CSRF. Pengganti action-form v1: x-dl2::action-form milik DevLib 2 adalah formulir
                berbasis config yang menggambar isiannya sendiri (slot dibuang), jadi layar Stoku memakai komponen ini.
 Input        : action, method (post | get | put | patch | delete), slot.
 Output       : <form class="sstock-aform"> dengan @csrf dan @method bila perlu.
 Dipakai oleh : stock-counts/*, reorder-orders/index, reports/expiry, sample, hp/count, components/report-filter.
 Status       : Aktif.
--}}
@props(['action', 'method' => 'post'])
@php $m = strtolower($method); @endphp
<form method="{{ $m === 'get' ? 'get' : 'post' }}" action="{{ $action }}" {{ $attributes->class(['sstock-aform']) }}>
    @if ($m !== 'get')@csrf @endif
    @if (in_array($m, ['put', 'patch', 'delete'], true))@method(strtoupper($m)) @endif
    {{ $slot }}
</form>
