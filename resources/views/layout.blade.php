{{-- REMARK
 Tujuan       : Kerangka HTML semua halaman SStock (Simple Stock): aset DevLib + tema + Tailwind.
 Sumber hulu  : ../devlib2 (@devlib2, x-dl2::theme-boot), resources/css/app.css.
 Input        : title, bodyClass dari view anak.
 Dipakai oleh : @extends oleh semua view SStock.
 Status       : Aktif.
--}}
<!DOCTYPE html>
<html lang="{{ \Devlib2\Locale::lang() }}" data-dl2-tz="{{ config('devlib.timezone') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="{{ \Devlib2\Device::viewport(request()) }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ \Devlib2\Lang::text($title ?? 'Stoku') }} — Stoku</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <x-dl2::theme-boot />
    @devlib2
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="dl2-body {{ $bodyClass ?? '' }} {{ \Devlib2\Device::view(request()) === \Devlib2\Device::PHONE ? 'sstock-on-phone' : '' }}">
    @yield('body')
    {{-- Logout otomatis: hanya halaman berlogin (tamu tidak punya sesi); lamanya = umur sesi server, ping memperpanjang sesi. --}}
    @auth
        <x-dl2::idle-logout :minutes="config('session.lifetime')" logout-url="{{ route('logout') }}" ping-url="{{ route('session.ping') }}" login-url="{{ route('login') }}" />
    @endauth
    <script>
        // Cegah kirim ganda (klik Simpan dua kali): form yang baru saja terkirim menolak kiriman kedua selama 8 detik.
        // Fase tangkap = menolak; fase gelembung = menandai hanya bila validasi form tidak membatalkan kiriman.
        document.addEventListener('submit', e => { if (e.target.dataset.sentAt && Date.now() - e.target.dataset.sentAt < 8000) { e.preventDefault(); e.stopImmediatePropagation(); } }, true);
        document.addEventListener('submit', e => { if (!e.defaultPrevented) e.target.dataset.sentAt = Date.now(); });
    </script>
</body>
</html>
