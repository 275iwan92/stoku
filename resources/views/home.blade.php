{{-- REMARK
 Tujuan       : Layar utama SStock: Layer Shell + dashboard (KPI, peringatan Reorder Point).
 Sumber hulu  : HomeController (modules menurut peran, KPI, daftar item di/bawah ROP dan yang hampir kedaluwarsa), partial partials.reorder-alert dan partials.expiry-alert.
 Input        : modules, user, open, below, itemCount, stockValue, receivingToday, issuanceToday, valuation, pending.
 Output       : x-dl2::layer-shell; tiap menu terbuka sebagai tab (iframe) layar masing-masing.
 Dipakai oleh : route home.
 Status       : Aktif.
--}}
@extends('layout', ['title' => 'Home', 'bodyClass' => 'sstock-bare'])
@section('body')
    {{-- Help & support (semua peran) membuka User's Guide; Technical Guide hanya tampil sebagai tab untuk Administrator. --}}
    <x-dl2::layer-shell :config="['home' => ['id' => 'home', 'title' => 'Beranda', 'panel' => '#sstock-dash']]" :without="['chat', 'ai']" :help-url="route('help.user-guide')" :modules="$modules" :user="$user" :logout-action="route('logout')" :device-action="route('ui.set')" :fresh="$fresh" :csrf="csrf_token()" data-open="{{ $open }}" />
    {{-- Beranda (dashboard) = panel tab pertama shell (DevLib 2 layer-shell tidak punya slot dashboard). --}}
    <template id="sstock-dash">
            @include('partials.flash')
            @include('partials.hello', ['name' => $user['name']])
            <x-dl2::quote :quotes="\App\Support\FrameMedia::quoteLines('motivasi-harian.txt')" order="day" class="sstock-quote" />
            @include('partials.count-reminder')
            <x-dl2::kpi-row>
                <x-dl2::kpi label="Active items" :value="$itemCount" icon="fa-boxes-stacked" data-open-tab="ITM" class="sstock-link" role="link" tabindex="0" />
                <x-dl2::kpi label="At / below reorder point" :value="$below->count()" icon="fa-triangle-exclamation" :tone="$below->isNotEmpty() ? 'danger' : 'success'" good-when="down" data-open-tab="ITM" class="sstock-link" role="link" tabindex="0" />
                <x-dl2::kpi label="Near expiry / expired" :value="$expiring->count()" icon="fa-hourglass-end" :tone="$expiring->isNotEmpty() ? ($expiring->contains(fn ($i) => $i->expiryState() === 'KEDALUWARSA') ? 'danger' : 'warn') : 'success'" good-when="down" data-open-tab="R-EXP" class="sstock-link" role="link" tabindex="0" />
                <x-dl2::kpi label="Receiving today" :value="$receivingToday" icon="fa-truck-ramp-box" data-open-tab="RCV" class="sstock-link" role="link" tabindex="0" />
                <x-dl2::kpi label="Issuance today" :value="$issuanceToday" icon="fa-dolly" data-open-tab="ISS" class="sstock-link" role="link" tabindex="0" />
                @if ($stockValue !== null)
                    <x-dl2::kpi label="Stock value" :value="$stockValue" format="money" icon="fa-coins" data-open-tab="R-SUM" class="sstock-link" role="link" tabindex="0" />
                @endif
                @if ($pending !== null)
                    <x-dl2::kpi label="Documents waiting approval" :value="$pending" icon="fa-scale-balanced" :tone="$pending ? 'warn' : 'success'" good-when="down" />
                @endif
            </x-dl2::kpi-row>
            <div class="sstock-split">
                @if ($below->isNotEmpty())
                    <div>@include('partials.reorder-alert')</div>
                @endif
                @if ($expiring->isNotEmpty())
                    <div>@include('partials.expiry-alert')</div>
                @endif
                @if ($valuation)
                    <div class="sstock-box">
                        <div class="sstock-box-nav">
                            @if ($valuation['prev'])
                                <x-dl2::button variant="secondary" size="sm" icon="fa-chevron-left" label="Bulan lalu" :href="route('home', ['month' => $valuation['prev']])" />
                            @else
                                <span></span>
                            @endif
                            <strong>{{ $valuation['title'] }}</strong>
                            @if ($valuation['next'])
                                <x-dl2::button variant="secondary" size="sm" icon="fa-chevron-right" label="Bulan depan" :href="route('home', ['month' => $valuation['next']])" />
                            @else
                                <span></span>
                            @endif
                        </div>
                        <x-dl2::chart type="hbar" format="money" height="320px" :legend="false" title="Nilai Stok" subtitle="Per minggu"
                            :labels="$valuation['labels']" :series="[['name' => 'Nilai stok', 'data' => $valuation['values'], 'tone' => 'violet']]" source="stock_movements" />
                    </div>
                @endif
            </div>
    </template>
    @include('partials.music')
    {{-- Jendela / layar sempit tanpa pilihan tampilan (mis. login lewat tab lama atau mode Responsive DevTools): pindah ke Tampilan HP di Beranda juga (auto); hanya Beranda, tab-tab di dalamnya adalah iframe. --}}
    @if ((\Devlib2\Device::view(request()) === \Devlib2\Device::DESKTOP && (\Devlib2\Device::normalize(request()->cookie(\Devlib2\Device::COOKIE)) === null || \Devlib2\Device::autoChosen(request()))))
        <x-dl2::phone-offer :config="['action' => route('ui.set'), 'auto' => true]" />
    @endif
    {{-- Kotak ringkasan dan tombol ber-data-open-tab membuka menu yang sama sebagai tab kerja Layer Shell (pesan open-tab ke Shell). --}}
    <script>
        const shellEl = () => document.querySelector("[data-dl2=layer-shell]");
        const openTab = el => { const s = Devlib2.classes["layer-shell"].getInstance(shellEl()); if (s) s.open({id: el.dataset.openTab}); };
        const first = (n) => { const s = shellEl() && Devlib2.classes["layer-shell"].getInstance(shellEl()); if (s) { const c = shellEl().dataset.open; if (c) s.open({id: c}); } else if (n < 40) setTimeout(() => first(n + 1), 100); };
        document.addEventListener("DOMContentLoaded", () => first(0));
        document.addEventListener('click', e => { const el = e.target.closest('[data-open-tab]'); if (el) { e.preventDefault(); openTab(el); } });
        document.addEventListener('keydown', e => { const el = e.target.closest?.('[data-open-tab]'); if (el && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); openTab(el); } });
    </script>
@endsection
