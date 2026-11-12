// Baris dokumen stok (Penerimaan, Pengeluaran, Saldo Awal, Penyesuaian) di atas form-lines DevLib 2.
// form-lines v2 belum punya kolom lookup (cari barang ke server + isi kolom lain), rumus kolom (formula) dan satuan per barang; semuanya ditambahkan di sini
// lewat penggantian metode cell() / sync() pada instance, tanpa mengubah DevLib.
// Kolom (dari docs/form.blade.php): lookup + url + fill (pilih barang mengisi satuan, harga, stok), formula "a * b" / "a - b", key stock_qty (hasil konversi satuan masuk).
// Baris Penerimaan membawa packs (JSON ProductUnits::options) dan stock_unit: kolom unit menawarkan satuan stok + satuan masuk barang itu.

const D = window.Devlib2;

function enhance(lines) {
    if (lines.sstockDoc) return;
    lines.sstockDoc = true;
    const {esc} = D.h, P = D.formParity;
    const num = (v) => Number(P.parseNum(v)) || 0;
    const nf = new Intl.NumberFormat(D.config.locale || 'id', {maximumFractionDigits: 6});
    const fmt = (n) => nf.format(n);
    const ALL = window.sstockUnits || {};
    const label = (k) => ALL[k] ?? k;
    const info = (r) => { try { return JSON.parse(r.packs || ''); } catch (e) { return null; } };
    const factor = (r, p) => (r.unit === p.stock ? 1 : (p.own[r.unit] ?? p.global[r.unit] ?? null));
    const frozen = (r) => lines.ro || (lines.opts.rowState && lines.state.get(r) === 'saved');
    const formulaOf = (c) => { const m = /^\s*(\w+)\s*([*+-])\s*(\w+)\s*$/.exec(c.formula || ''); return m ? {a: m[1], op: m[2], b: m[3]} : null; };

    const compute = (r) => {
        lines.cols.forEach((c) => {
            const f = c.formula && formulaOf(c);
            if (!f) return;
            const a = num(r[f.a]), b = num(r[f.b]);
            r[c.key] = f.op === '*' ? a * b : f.op === '+' ? a + b : a - b;
        });
    };
    const converted = (r) => {
        if (lines.ro) return r.stock_unit && r.unit !== r.stock_unit ? `= ${fmt(num(r.stock_qty))} ${r.stock_unit}` : '';
        const p = info(r);
        if (!p || !r.unit || r.unit === p.stock) return '';
        const f = factor(r, p), q = num(r.qty);
        return f == null ? 'Faktor belum ada' : q > 0 ? `= ${fmt(q * +f)} ${p.stock}` : '';
    };
    const shown = (c, r) => (c.key === 'stock_qty' ? converted(r) : c.formula ? (r[c.key] === '' ? '' : fmt(num(r[c.key]))) : null);

    const cell0 = lines.cell.bind(lines);
    lines.cell = (c, r, i) => {
        const lab = `data-label="${esc(c.label)}"`;
        if (c.key === 'stock_qty' || c.formula) return `<td class="dl2-fl-ro dl2-fl-num" ${lab} data-k="${esc(c.key)}">${esc(shown(c, r))}</td>`;
        if (c.lookup) {
            const text = r[c.key + '_label'] ?? '';
            if (frozen(r)) return `<td class="dl2-fl-ro" ${lab}>${esc(text)}</td>`;
            return `<td ${lab}><div class="sstock-lk"><input type="hidden" data-k="${esc(c.key)}" value="${esc(r[c.key])}">`
                + `<input type="text" class="dl2-input dl2-input-sm" data-lk-q="${esc(c.key)}" aria-label="${esc(c.label)} ${i + 1}" value="${esc(text)}" autocomplete="off" placeholder="Cari kode / nama">`
                + '<ul class="sstock-lk-list" hidden></ul></div></td>';
        }
        if (c.key === 'unit' && c.type === 'select' && !frozen(r)) {
            const p = info(r);
            if (p) {
                const opt = (k, text) => `<option value="${esc(k)}" ${k === r.unit ? 'selected' : ''}>${esc(text)}</option>`;
                const known = new Set([p.stock, ...Object.keys(p.own)]);
                let html = '<option value=""></option>' + opt(p.stock, label(p.stock)) + Object.entries(p.own).map(([k, f]) => opt(k, `${label(k)} (1 = ${fmt(+f)} ${p.stock})`)).join('');
                html += '<optgroup label="Satuan lain">' + Object.keys(ALL).filter((k) => !known.has(k)).map((k) => opt(k, p.global[k] ? `${label(k)} (1 = ${fmt(+p.global[k])} ${p.stock})` : `${label(k)} (faktor belum ada)`)).join('') + '</optgroup>';
                return `<td ${lab}><select class="dl2-select dl2-select-sm" data-k="unit" aria-label="${esc(c.label)} ${i + 1}" required>${html}</select></td>`;
            }
        }
        return cell0(c, r, i);
    };
    const sync0 = lines.sync.bind(lines);
    lines.sync = () => { lines.rows.forEach(compute); sync0(); };

    const refreshRow = (tr) => {
        const r = lines.rows[+tr.dataset.i];
        if (!r) return;
        lines.cols.forEach((c) => {
            const s = shown(c, r);
            const td = s === null ? null : tr.querySelector(`td[data-k="${c.key}"]`);
            if (td) td.textContent = s;
        });
    };
    lines.el.addEventListener('input', (e) => { const tr = e.target.closest('tr[data-i]'); if (tr && e.target.closest('[data-k]')) refreshRow(tr); });
    lines.el.addEventListener('change', (e) => { const tr = e.target.closest('tr[data-i]'); if (tr && e.target.closest('[data-k]')) refreshRow(tr); });

    // Pencarian barang ke server (Devlib2\Lookup::search): daftar hasil di bawah kotak; memilih mengisi kolom lain sesuai fill.
    const timers = new WeakMap();
    lines.el.addEventListener('input', (e) => {
        const q = e.target.closest('[data-lk-q]');
        if (!q) return;
        const c = lines.cols.find((x) => x.key === q.dataset.lkQ), list = q.parentElement.querySelector('.sstock-lk-list');
        clearTimeout(timers.get(q));
        if (q.value.trim().length < 2) { list.hidden = true; return; }
        timers.set(q, setTimeout(async () => {
            try {
                const res = await fetch(`${c.url}?q=${encodeURIComponent(q.value.trim())}`, {credentials: 'same-origin', headers: {Accept: 'application/json'}});
                const rows = res.ok ? await res.json() : [];
                list.__rows = rows;
                list.innerHTML = rows.length
                    ? rows.map((x, n) => `<li><button type="button" data-lk-pick="${n}">${esc(x[c.labelKey || 'label'] ?? '')}</button></li>`).join('')
                    : '<li class="sstock-lk-none">Tidak ada hasil</li>';
                list.hidden = false;
            } catch (err) { list.hidden = true; }
        }, 250));
    });
    lines.el.addEventListener('click', (e) => {
        const b = e.target.closest('[data-lk-pick]');
        if (!b) return;
        const tr = b.closest('tr[data-i]'), list = b.closest('.sstock-lk-list'), q = list.parentElement.querySelector('[data-lk-q]');
        const c = lines.cols.find((x) => x.key === q.dataset.lkQ), row = list.__rows[+b.dataset.lkPick], r = lines.rows[+tr.dataset.i];
        r[c.key] = row[c.valueKey || 'id'];
        r[c.key + '_label'] = row[c.labelKey || 'label'];
        Object.entries(c.fill || {}).forEach(([target, source]) => { r[target] = row[source] ?? ''; });
        if (c.fill && c.fill.packs) r.packs = row.packs ?? '';
        compute(r);
        lines.paint();
        lines.touch(false);
        const next = lines.body.querySelector(`tr[data-i="${tr.dataset.i}"] [data-k="qty"], tr[data-i="${tr.dataset.i}"] [data-k="counted_qty"]`);
        if (next) next.focus();
    });
    document.addEventListener('click', (e) => { if (!e.target.closest('.sstock-lk')) lines.el.querySelectorAll('.sstock-lk-list').forEach((l) => { l.hidden = true; }); });

    lines.rows.forEach(compute);
    lines.paint();
}

let tries = 0;
function attach() {
    const el = document.querySelector('[data-dl2="form-lines"][data-sstock-doc]');
    if (!el) return;
    const lines = D.classes['form-lines'] && D.classes['form-lines'].getInstance(el);
    if (!lines) { if (tries++ < 100) setTimeout(attach, 50); return; }
    enhance(lines);
}
if (D) document.addEventListener('DOMContentLoaded', attach);
