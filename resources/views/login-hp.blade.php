{{-- REMARK
 Tujuan       : Halaman login tampilan HP (smartphone potret): memakai x-dl2::phone-login dari DevLib (satu kolom mengalir 320-440 px, isian besar, tautan Tampilan Desktop hanya untuk jendela komputer yang dipersempit; ponsel asli selalu Tampilan HP).
 Sumber hulu  : AuthController::show (error, username); DevLib x-dl2::phone-login, Devlib2\Device.
 Input        : error (pesan login gagal), username (isian terakhir).
 Output       : form login yang diposting ke route login.attempt.
 Dipakai oleh : route login bila perangkat = hp (ponsel atau pilihan pengguna). Latar: x-hp-wallpaper (foto tegak).
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Sign in', 'bodyClass' => 'sstock-bare sstock-hp'])
@section('body')
    <x-hp-wallpaper>
        <x-dl2::phone-login :config="['token' => csrf_token()]" :action="route('login.attempt')" :error="$error ?? ($errors->first() ?: null)" :username="$username" :forgot-url="route('password.request')" :brand="config('devlib.brand.name')" :tagline="config('devlib.brand.tagline')" :device-action="\App\Support\HpShell::deviceAction()">
            @if (session('status'))<x-dl2::alert tone="success" :message="session('status')" />@endif
            @php($v = app('devlib.version'))<span class="sstock-ver" title="{{ $v['title'] }}">{{ $v['text'] }} @if ($v['letter'])<span class="sstock-ver__stage">{{ $v['letter'] }}</span>@endif</span>
        </x-dl2::phone-login>
    </x-hp-wallpaper>
@endsection
