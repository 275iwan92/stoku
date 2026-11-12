{{-- REMARK
 Tujuan       : Sapaan Beranda: SATU baris "Selamat siang, <nama depan>" (x-dl2::greeting, tanpa emoji) + di bawahnya tanggal dan jam kecil (x-dl2::clock mode=line; di HP tanggal singkat).
                Cuaca (x-dl2::weather) mengikuti posisi perangkat; tempat CADANGAN dari layar Perusahaan (Company::weather). Tanpa lokasi dan tanpa cadangan kartu cuaca tidak tampil dan sapaan tetap rapi.
                Desktop: kartu cuaca di ruang kosong kanan sapaan. HP (body.sstock-on-phone, view phone): chip kecil di samping tanggal.
 Sumber hulu  : App\Support\Company::weather, Devlib2\Device::view.
 Input        : name (nama lengkap pengguna; diambil nama depannya).
 Dipakai oleh : home (desktop), hp.home.
 Status       : Aktif.
--}}
@php
    $phone = \Devlib2\Device::view(request()) === \Devlib2\Device::PHONE;
    $wx = \App\Support\Company::weather();
    $wxProps = ['city' => $wx['city'] ?? null, 'lat' => $wx['lat'] ?? null, 'lon' => $wx['lon'] ?? null];
@endphp
@if ($phone)
    <div class="sstock-hello sstock-hello--hp">
        <x-dl2::greeting :name="strtok(trim($name), ' ')" />
        <div class="sstock-hello__meta">
            <x-dl2::clock mode="line" />
            <x-dl2::net-line :ping-url="route('devlib.ping')" :client-ip="app('devlib.net')['client_ip']" :server-ip="app('devlib.net')['server_ip']" :client-host="app('devlib.net')['client_host']" :server-host="app('devlib.net')['server_host']" :interval="10" />
            <x-dl2::weather variant="chip" :city="$wxProps['city']" :lat="$wxProps['lat']" :lon="$wxProps['lon']" />
        </div>
    </div>
@else
    <div class="sstock-hello">
        <div>
            <x-dl2::greeting :name="strtok(trim($name), ' ')" />
            <x-dl2::clock mode="line" />
            <x-dl2::net-line :ping-url="route('devlib.ping')" :client-ip="app('devlib.net')['client_ip']" :server-ip="app('devlib.net')['server_ip']" :client-host="app('devlib.net')['client_host']" :server-host="app('devlib.net')['server_host']" :interval="10" />
        </div>
        <x-dl2::weather variant="card" :city="$wxProps['city']" :lat="$wxProps['lat']" :lon="$wxProps['lon']" />
    </div>
@endif
