<?php

namespace App\Http\Middleware;

use Closure;
use Devlib2\Lang;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/* REMARK
 * Tujuan       : Menerjemahkan teks tampilan komponen DevLib 2 ke Indonesia. Layar Stoku ditulis English (label, judul kolom, hint, ...) dan v1 menerjemahkannya di PHP lewat Lang::text;
 *                v2 menaruh teks itu di atribut data-dl2-config (JSON) tanpa penerjemahan, jadi respons HTML diproses di sini memakai kamus App\Support\Indonesian (Devlib2\Lang).
 * Sumber hulu  : Devlib2\Lang::text (kamus App\Support\Indonesian); teks tanpa terjemahan tetap apa adanya.
 * Output       : respons HTML dengan nilai kunci teks (label, title, hint, ...) di data-dl2-config sudah diterjemahkan.
 * Dipakai oleh : grup middleware web (bootstrap/app.php).
 * Status       : Aktif.
 */
class TranslateDevlib
{
    private const KEYS = ['label', 'title', 'subtitle', 'hint', 'tooltip', 'placeholder', 'empty', 'message', 'sub', 'text', 'legend', 'caption', 'addLabel', 'header', 'searchPlaceholder', 'newRecord', 'newLabel', 'search', 'submit'];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html') || $response->isRedirection()) {
            return $response;
        }
        $html = $response->getContent();
        if (! is_string($html) || ! str_contains($html, 'data-dl2-config="')) {
            return $response;
        }
        $out = preg_replace_callback('/data-dl2-config="([^"]*)"/', function (array $m) {
            $data = json_decode(html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5), true);
            if (! is_array($data)) {
                return $m[0];
            }

            return 'data-dl2-config="'.e(json_encode(self::walk($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)).'"';
        }, $html);
        if (is_string($out)) {
            $original = $response->original ?? null;   // setContent menimpa original (objek view yang dipakai tes)
            $response->setContent($out);
            if ($original !== null && property_exists($response, 'original')) {
                $response->original = $original;
            }
        }

        return $response;
    }

    private static function walk(array $data): array
    {
        foreach ($data as $k => $v) {
            if (is_array($v)) {
                $data[$k] = self::walk($v);
            } elseif (is_string($v) && is_string($k) && in_array($k, self::KEYS, true) && preg_match('/[A-Za-z]/', $v)) {
                $data[$k] = html_entity_decode(Lang::text($v), ENT_QUOTES | ENT_HTML5);
            }
        }

        return $data;
    }
}
