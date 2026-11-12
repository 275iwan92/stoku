<?php

namespace App\Support;

/* REMARK
 * Tujuan       : Daftar gambar latar dan kutipan untuk x-dl2::frame (halaman masuk). DevLib 2 tidak membaca folder media (v1 Frame membaca sendiri), jadi aplikasi yang menyediakannya.
 * Sumber hulu  : public/media/bg/<folder>/*.jpg (berkas -portrait dipasangkan), public/media/text/<berkas>.txt (baris "teks | penulis", # = komentar).
 * Output       : images() = [{src, portrait}], quotes() = [{t, a}].
 * Dipakai oleh : login, forgot-password, reset-password, home (kutipan harian).
 * Status       : Aktif.
 */
final class FrameMedia
{
    /** @return array<int, array{src: string, portrait: ?string}> */
    public static function images(string $folder): array
    {
        $root = public_path('media'.DIRECTORY_SEPARATOR.'bg'.DIRECTORY_SEPARATOR.$folder);
        $files = is_dir($root) ? (glob($root.DIRECTORY_SEPARATOR.'*.{jpg,jpeg,JPG,JPEG}', GLOB_BRACE) ?: []) : [];
        $files = array_values(array_filter($files, fn ($f) => ! str_contains(basename($f), '-portrait.')));
        sort($files);

        return array_map(function ($f) use ($folder) {
            $portrait = preg_replace('/\.(jpe?g)$/i', '-portrait.$1', $f);

            return [
                'src' => '/media/bg/'.$folder.'/'.basename($f),
                'portrait' => is_file($portrait) ? '/media/bg/'.$folder.'/'.basename($portrait) : null,
            ];
        }, $files);
    }

    /** Bentuk "teks | penulis" untuk x-dl2::quote. @return array<int, string> */
    public static function quoteLines(string $file): array
    {
        return array_map(fn ($q) => $q['t'].($q['a'] !== '' ? ' | '.$q['a'] : ''), self::quotes($file));
    }

    /** @return array<int, array{t: string, a: string}> */
    public static function quotes(string $file): array
    {
        $path = public_path('media'.DIRECTORY_SEPARATOR.'text'.DIRECTORY_SEPARATOR.$file);
        if (! is_file($path)) {
            return [];
        }
        $out = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$t, $a] = array_map('trim', explode('|', $line, 2) + [1 => '']);
            $out[] = ['t' => $t, 'a' => $a];
        }

        return $out;
    }
}
