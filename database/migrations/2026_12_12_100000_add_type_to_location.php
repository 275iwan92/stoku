<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/* REMARK
 * Tujuan       : Jenis lokasi penyimpanan (lemari | gudang_tertutup | gudang_terbuka | area_luas); kosong = belum ditentukan.
 * Output       : kolom locations.type (nullable).
 * Status       : Aktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', fn (Blueprint $t) => $t->string('type', 20)->nullable()->after('name'));
    }

    public function down(): void
    {
        Schema::table('locations', fn (Blueprint $t) => $t->dropColumn('type'));
    }
};
