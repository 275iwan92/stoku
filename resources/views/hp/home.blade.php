{{-- REMARK
 Tujuan       : Beranda tampilan HP: ringkasan stok sebagai kartu besar (angka + nama) yang membuka modulnya, dan pengingat Stok Opname.
 Sumber hulu  : HpController@home (cards, countReminder).
 Input        : cards (label, value, icon, code, tone), countReminder (CountSchedule::state atau null).
 Output       : kartu tautan ke hp.open / hp.counts, pengingat opname.
 Dipakai oleh : HomeController bila perangkat = hp.
 Status       : Aktif.
--}}
@extends('hp.layout')
@section('content')
    @include('partials.flash')
    @include('partials.hello', ['name' => auth()->user()->name])
    @if ($countReminder)
        @php $r = $countReminder; @endphp
        <x-dl2::phone-link icon="fa-solid fa-clipboard-check" :url="route('hp.counts')" :tone="$r['running'] ? 'info' : ($r['tone'] === 'neutral' ? 'info' : $r['tone'])"
            :label="raw($r['running'] ? 'Stok Opname '.$r['running']->doc_no.' sedang berjalan ('.$r['running']->statusLabel().', terisi '.$r['running_progress'].')' : $r['headline'].' '.$r['detail'])" />
    @endif
    @if (auth()->user()->canAccess('ITM'))
        <x-dl2::phone-link icon="fa-solid fa-magnifying-glass" :url="route('hp.find')" label="Cari Barang" />
    @endif
    {{-- DevLib 2 phone-tiles dibangun dari config (slot dibuang): satu ubin per kartu, jumlahnya sebagai lencana. --}}
    <x-dl2::phone-tiles :config="['tiles' => collect($cards)->map(fn ($c) => ['id' => $c['code'], 'label' => (string) $c['label'], 'icon' => $c['icon'], 'href' => \App\Support\HpShell::url($c['code']), 'badge' => \Devlib2\Locale::number($c['value'], 0), 'tone' => $c['tone'] ?? ''])->values()->all()]" />
@endsection
