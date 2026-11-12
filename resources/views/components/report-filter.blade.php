{{-- REMARK
 Tujuan       : Bilah saring laporan (GET): periode dari-sampai, kolom tambahan lewat slot, tombol Show. Hasil ada di query string sehingga laporan bisa di-refresh / dibagikan.
 Input        : from, to (Carbon); slot = field tambahan.
 Output       : x-action-form method GET.
 Dipakai oleh : semua view reports.*.
 Status       : Aktif.
--}}
@props(['from', 'to'])
<x-action-form method="get" :action="url()->current()" class="sstock-report-filter">
    <x-dl2::form.row :cols="4">
        <x-dl2::field.date name="from" label="From date" :value="$from->format('Y-m-d')" />
        <x-dl2::field.date name="to" label="To date" :value="$to->format('Y-m-d')" />
        {{ $slot }}
    </x-dl2::form.row>
    <x-dl2::action-bar>
        <x-dl2::button type="submit" icon="fa-eye" label="Show" size="sm" />
    </x-dl2::action-bar>
</x-action-form>
