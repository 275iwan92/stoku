<?php

namespace App\Providers;

use App\Support\Company;
use App\Support\GeoLookup;
use App\Support\Indonesian;
use App\Support\ItemLookups;
use Devlib2\AppVersion;
use Devlib2\Lookup;
use Devlib2\NetworkInfo;
use Devlib2\PrintDoc\Document;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // DevLib 2 tidak mendaftarkan rute sendiri untuk ping dan lookup: aplikasi menyediakannya.
        // Ping x-dl2::net-line: 204 tanpa isi, tanpa sesi/login/cookie (di luar grup web), hanya dibatasi lajunya.
        Route::middleware('throttle:120,1')->get('/devlib/ping', fn () => response()->noContent(204, ['Cache-Control' => 'no-store']))->name('devlib.ping');
        // Lookup remote (Devlib2\Lookup::search): hanya untuk pengguna yang login (config devlib.lookup_middleware).
        Route::middleware(config('devlib.lookup_middleware', ['web', 'auth']))
            ->get('/_devlib2-lookup/{name}', fn (Request $r, string $name) => response()->json(Lookup::search($name, (string) $r->query('q', ''), $r->except('q'))))
            ->where('name', '[A-Za-z0-9_-]+')->name('devlib2.lookup');
        // DevLib 2 tidak menerjemahkan label komponen sendiri (v1 memanggil Lang::text di PHP): layar Stoku ditulis English dan kamus Indonesian menerjemahkannya.
        // Prakompilasi Blade (prepareStringsForCompilationUsing, sebelum tag komponen dikompilasi): atribut teks tetap (label, title, hint, ...) pada tag <x-dl2::*> dibungkus Devlib2\Lang::text; ekspresi dinamis (:label=...) tidak disentuh.
        Blade::prepareStringsForCompilationUsing(function (string $value) {
            if (! str_contains($value, '<x-dl2::')) {
                return $value;
            }

            return preg_replace_callback('/<x-dl2::[\w.\-]+(?:\s+(?:"[^"]*"|\'[^\']*\'|[^>"\'])*)?\/?>/', function (array $tag) {
                $keys = 'label|title|subtitle|hint|tooltip|placeholder|add-label|empty|message|sub|text|legend|caption';
                $t = preg_replace_callback(
                    '/(?<=\s)(:?)('.$keys.')="([^"]*)"/',
                    function (array $a) {
                        if ($a[1] === ':') {   // ekspresi dinamis: dibungkus penerjemah (nilai bukan string lolos apa adanya)
                            return ':'.$a[2].'="\App\Support\Indonesian::tr('.$a[3].')"';
                        }
                        if (str_contains($a[3], '{') || ! preg_match('/[A-Za-z]/', $a[3])) {
                            return $a[0];
                        }

                        return ':'.$a[2].'="\App\Support\Indonesian::tr(\''.addcslashes(html_entity_decode($a[3], ENT_QUOTES), "'\\").'\')"';
                    },
                    $tag[0]
                );
                // x-dl2::button: prop icon v2 berarti tombol hanya-ikon; ikon di samping teks (v1) dipasang resources/js/app.js dari data-ico.
                return str_starts_with($t, '<x-dl2::button') ? preg_replace('/(?<=\s)icon="([^"{}]*)"/', 'data-ico="$1"', $t) : $t;
            }, $value);
        });
        ItemLookups::register();
        GeoLookup::register();
        Indonesian::register();
        Document::companyUsing(fn () => Company::kop());   // satu sumber kop semua cetakan: layar Perusahaan
        // Baris jaringan x-dl2::net-line (IP client -> IP server + status koneksi) di bawah nama pengguna, semua pengguna: keluhan bisa ditelusuri dari mana asalnya.
        // Ping memakai rute bawaan DevLib /devlib/ping (tanpa sesi): session.ping memperpanjang sesi 30 menit dan login-redirect-nya bisa tampak sebagai gagal.
        // Versi di judul logo (menu kiri) dan halaman login: ver 0.<jumlah commit git> + huruf tahap (APP_STAGE) lewat Devlib2\AppVersion.
        $this->app->bind('devlib.version', fn () => AppVersion::of(base_path()));
        $this->app->bind('devlib.net', fn () => NetworkInfo::of(request()));

        // E-mail reset password berbahasa Indonesia; tautan ke layar password baru, berlaku sesuai config auth.passwords.users.expire.
        ResetPassword::toMailUsing(function (object $user, string $token) {
            $url = route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()]);
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new MailMessage)->subject('Atur ulang password '.config('app.name'))
                ->greeting('Halo '.$user->name.',')
                ->line('Kami menerima permintaan untuk mengatur ulang password akun '.config('app.name').' Anda.')
                ->action('Atur password baru', $url)
                ->line("Tautan ini berlaku {$minutes} menit. Jika bukan Anda yang meminta, abaikan e-mail ini; password Anda tidak berubah.")
                ->salutation(config('app.name'));
        });
    }
}
