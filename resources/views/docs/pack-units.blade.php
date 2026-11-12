{{-- REMARK
 Tujuan       : Daftar nama satuan (kode => label) untuk baris Penerimaan: kolom satuan menawarkan satuan stok + satuan masuk milik barangnya (grup "Satuan lain" untuk satuan tanpa faktor), dan kolom "= In stock unit" menghitung hasil konversi sebelum Confirm.
                Perhitungan resmi tetap di server (StockDocuments::toStockUnit); logika tampilan ada di resources/js/doc-lines.js. Dokumen terkunci menampilkan qty tersimpan (stock_qty dari server).
 Sumber hulu  : baris form (packs = JSON ProductUnits::options, stock_unit, unit, qty, stock_qty), daftar satuan aktif ($units).
 Input        : units (kode => label).
 Output       : window.sstockUnits untuk doc-lines.js.
 Dipakai oleh : docs/form.blade.php (Penerimaan).
 Status       : Aktif.
--}}
<script>window.sstockUnits = @json($units);</script>
