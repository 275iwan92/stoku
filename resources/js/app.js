// Layar tinjau Stok Opname: ringkasan (jumlah / nilai lebih-kurang) dihitung server, jadi halaman dimuat ulang setelah sel tersimpan.
document.addEventListener("saved.dl2.row-sheet", (e) => {
    if (e.target.closest('[data-reload-on-save]')) setTimeout(() => location.reload(), 600);
});

// Simpan Cadangan di lembar input Stok Opname: isian sudah tersimpan per sel; tombol ini memastikannya. Layar mengirim jumlah baris terisi yang dilihatnya, server membandingkan dengan databasenya
// (dicoba beberapa kali supaya simpan sel yang masih berjalan sempat selesai).
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-count-save]');
    if (!btn) return;
    const host = document.querySelector('[data-dl2="row-sheet"]'), sheet = host && window.Devlib2?.classes['row-sheet']?.getInstance(host);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const toast = window.Devlib2?.toast;
    if (!sheet || !toast) return;
    btn.disabled = true;
    let res = null;
    for (let i = 0; i < 3; i++) {
        await new Promise((r) => setTimeout(r, 700));
        const seen = sheet.rows.filter((r) => (r.counted_qty !== '' && r.counted_qty != null) || r.skipped).length;
        try {
            const r = await fetch(btn.dataset.countSave, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ seen }) });
            res = { ok: r.ok, ...(await r.json().catch(() => ({}))) };
        } catch {
            res = { ok: false, error: 'Tidak bisa menghubungi server. Periksa koneksi, lalu coba lagi.' };
        }
        if (res.ok) break;
    }
    toast(res.ok ? res.message : (res.error || 'Gagal menyimpan.'), {tone: res.ok ? 'success' : 'danger'});
    btn.disabled = false;
});

// Hitung Opname tampilan HP (aktif hanya di halaman yang punya [data-hp-count]).
import './hp-count';

// Cari Barang tampilan HP: isi kolom terpilih saat fokus supaya pindai berikutnya menimpa.
import './hp-find';

// Layar yang dibuka di dalam iframe (tab Layer Shell / kerangka HP): tanda untuk aturan CSS aplikasi.
if (window.self !== window.top) document.body.classList.add('sstock-framed');

// Ikon Font Awesome ("fa-boxes-stacked", "fa-solid fa-clock"): DevLib 2 hanya punya set SVG sendiri; nama fa-* diteruskan ke Font Awesome (dimuat di layout).
if (window.Devlib2?.icons) {
    const get = window.Devlib2.icons.get.bind(window.Devlib2.icons);
    window.Devlib2.icons.get = (name, opts) => (/^fa-/.test(name || '') ? `<i class="fa-solid ${name}" aria-hidden="true"></i>` : get(name, opts));
}

// Baris dokumen stok: pencarian barang, rumus kolom dan konversi satuan di atas form-lines.
import './doc-lines';

// Ikon gaya v1 yang DevLib 2 tidak menggambar sendiri: (1) tombol dengan data-ico (dipasang Blade dari prop icon), (2) .dl2-pic berisi nama Font Awesome (kerangka HP: tab, ubin, tautan).
const ICON_ALIAS = {new: 'plus', back: 'arrow-left', save: 'save'};
function paintIcons(root) {
    if (!root.querySelectorAll) return;
    root.querySelectorAll('[data-ico]:not([data-ico-done])').forEach((b) => {
        b.dataset.icoDone = '1';
        const name = b.dataset.ico, D = window.Devlib2;
        let html = '';
        if (/^fa-/.test(name)) html = `<i class="fa-solid ${name}" aria-hidden="true"></i> `;
        else if (D?.icons?.names?.().includes(ICON_ALIAS[name] || name)) html = D.icons.get(ICON_ALIAS[name] || name, {size: 16}) + ' ';
        if (html) b.insertAdjacentHTML('afterbegin', html);
    });
    root.querySelectorAll('.dl2-pic').forEach((s) => {
        const t = s.textContent.trim();
        if (/^[a-z][a-z0-9-]{2,}$/.test(t) && !/^fa-/.test(t)) s.innerHTML = `<i class="fa-solid fa-${t}" aria-hidden="true"></i>`;
        else if (/^(fa-[\w-]+\s*)+$/.test(t)) s.innerHTML = `<i class="${/\bfa-(solid|regular|brands)\b/.test(t) ? '' : 'fa-solid '}${t}" aria-hidden="true"></i>`;
    });
}
document.addEventListener('DOMContentLoaded', () => {
    paintIcons(document);
    new MutationObserver((ms) => ms.forEach((m) => m.addedNodes.forEach((n) => n.nodeType === 1 && paintIcons(n)))).observe(document.body, {childList: true, subtree: true});
});
