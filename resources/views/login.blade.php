{{-- REMARK
 Tujuan       : Halaman login Simple Stock (sstock), memakai x-dl2::frame + x-dl2::login-box dari DevLib.
 Sumber hulu  : DevLib (Frame, LoginBox), gambar latar public/media/bg/sstock, kutipan public/media/text/motivasi.txt (salinan DevLib).
 Input        : error (pesan login gagal), username (isian terakhir) dari AuthController::show.
 Output       : form login yang diposting ke route login.attempt.
 Dipakai oleh : route login.
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Sign in', 'bodyClass' => 'sstock-bare'])
@section('body')
    <x-dl2::frame :image-list="\App\Support\FrameMedia::images('sstock')" bg-order="random" box="right" color="light" widgets="brand@top-left, clock@top-right, quote@bottom-left" :quote-list="\App\Support\FrameMedia::quotes('motivasi.txt')" :brand="config('devlib.brand.name')" :tagline="config('devlib.brand.tagline')">
        @if (session('status'))<x-dl2::alert tone="success" :message="session('status')" />@endif
        <x-dl2::login-box :config="['token' => csrf_token()]" show="brand, title, username, password, forgot, button" :forgot-url="route('password.request')" :action="route('login.attempt')" :error="$error ?? ($errors->first() ?: null)" :username="$username" :brand="config('devlib.brand.name')" :tagline="config('devlib.brand.tagline')" />
        @php($v = app('devlib.version'))<span class="sstock-ver" title="{{ $v['title'] }}">{{ $v['text'] }} @if ($v['letter'])<span class="sstock-ver__stage">{{ $v['letter'] }}</span>@endif</span>
    </x-dl2::frame>
    {{-- Layar sempit (< 600 px) tanpa pilihan tampilan: langsung Tampilan HP (auto); popup tetap sebagai cadangan; di layar lebar tidak pernah terlihat. --}}
    @if ((\Devlib2\Device::view(request()) === \Devlib2\Device::DESKTOP && (\Devlib2\Device::normalize(request()->cookie(\Devlib2\Device::COOKIE)) === null || \Devlib2\Device::autoChosen(request()))))
        <x-dl2::phone-offer :config="['action' => route('ui.set'), 'auto' => true]" />
    @endif
@endsection
