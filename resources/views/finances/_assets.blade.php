<style>
    /* Barras y montos */
    .fbar { height: 10px; border-radius: 999px; background: #efe5f7; overflow: hidden; }
    .fbar > span { display: block; height: 100%; }
    .fbar > span.ok { background: linear-gradient(90deg,#7bc59a,#3f9e6b); }
    .fbar > span.mid { background: linear-gradient(90deg,#b07fd8,#8a55be); }
    .money { font-variant-numeric: tabular-nums; white-space: nowrap; }
    .fmini { font-size: 12px; color: #8a72a4; }

    /* Tira de totales */
    .ministrip { display: flex; gap: 26px; flex-wrap: wrap; align-items: center; padding: 14px 18px; }
    .ministrip .it { font-size: 12px; color: #8a72a4; text-transform: uppercase; letter-spacing: .05em; }
    .ministrip .it b { display: block; font-size: 20px; color: #43275b; margin-top: 2px; }

    /* Toolbar */
    .ftoolbar { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; justify-content: space-between; margin-bottom: 14px; }
    .ftoolbar .filters { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
    .ftoolbar input[type="search"], .ftoolbar input.search { min-width: 240px; }
    .ftoolbar select { min-width: 150px; }

    /* Tabla */
    .ftable-wrap { background:#fff; border:1px solid #e7dcf3; border-radius:16px; overflow:hidden; }
    table.ftable { width: 100%; border-collapse: collapse; }
    table.ftable th, table.ftable td { padding: 12px 14px; text-align: left; border-bottom: 1px solid #f1eaf8; vertical-align: middle; }
    table.ftable thead th { font-size: 11px; text-transform: uppercase; letter-spacing: .06em; color: #8a72a4; background: #faf6ff; user-select: none; }
    table.ftable thead th[data-sort] { cursor: pointer; }
    table.ftable thead th[data-sort]:hover { color: #6b4a86; }
    table.ftable tbody tr:hover { background: #fcfaff; }
    table.ftable td.num, table.ftable th.num { text-align: right; }
    table.ftable .sub { font-size: 12px; color: #9b8ab0; }
    .ftable-empty { padding: 26px; text-align: center; color: #9b8ab0; }
    .rowact { display: flex; gap: 6px; justify-content: flex-end; }

    /* Modal */
    .modal { display: none; position: fixed; inset: 0; background: rgba(45,28,66,.45); z-index: 1000; padding: 38px 16px; overflow: auto; }
    .modal.open { display: block; }
    .modal-box { background:#fff; border-radius: 20px; width: 100%; max-width: 560px; margin: 0 auto; padding: 24px; box-shadow: 0 30px 90px rgba(67,39,91,.35); }
    .modal-box.lg { max-width: 680px; }
    .modal-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; }
    .modal-head h3 { margin: 0; color: #43275b; }
    .modal-x { background: none; border: 0; font-size: 22px; line-height: 1; color: #9b8ab0; cursor: pointer; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; }
    .modal .field { margin-bottom: 12px; }
    .modal .field label { display:block; font-size: 12px; color: #6b5a7e; margin-bottom: 4px; font-weight: 600; }
    .modal .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    @media (max-width: 560px) { .modal .grid2 { grid-template-columns: 1fr; } }
</style>

<script>
(function () {
    // ----- Modales -----
    document.addEventListener('click', function (e) {
        const open = e.target.closest('[data-modal-open]');
        if (open) { document.getElementById(open.dataset.modalOpen)?.classList.add('open'); return; }
        if (e.target.closest('[data-modal-close]')) { e.target.closest('.modal')?.classList.remove('open'); return; }
        if (e.target.classList.contains('modal')) { e.target.classList.remove('open'); }
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') document.querySelectorAll('.modal.open').forEach(m => m.classList.remove('open'));
    });

    // ----- Numeración (#) de filas visibles -----
    function renumber(table) {
        if (!table) return;
        let n = 0;
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            const cell = tr.querySelector('.rownum');
            if (!cell) return;
            if (tr.style.display === 'none') return;
            cell.textContent = ++n;
        });
    }

    // ----- Filtro (buscador + estado) -----
    function applyFilter(tableId) {
        const table = document.getElementById(tableId);
        if (!table) return;
        const q = (document.querySelector('[data-table-filter="' + tableId + '"]')?.value || '').toLowerCase();
        const st = document.querySelector('[data-status-filter="' + tableId + '"]')?.value || '';
        table.querySelectorAll('tbody tr').forEach(function (tr) {
            const okText = tr.textContent.toLowerCase().includes(q);
            const okSt = !st || tr.dataset.status === st;
            tr.style.display = (okText && okSt) ? '' : 'none';
        });
        renumber(table);
    }
    document.querySelectorAll('[data-table-filter]').forEach(i => i.addEventListener('input', () => applyFilter(i.dataset.tableFilter)));
    document.querySelectorAll('[data-status-filter]').forEach(s => s.addEventListener('change', () => applyFilter(s.dataset.statusFilter)));

    // ----- Orden por columna -----
    document.querySelectorAll('table.ftable thead th[data-sort]').forEach(function (th) {
        th.addEventListener('click', function () {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            const idx = Array.prototype.indexOf.call(th.parentNode.children, th);
            const type = th.dataset.sort;
            const dir = th.dataset.dir === 'asc' ? -1 : 1;
            th.dataset.dir = dir === 1 ? 'asc' : 'desc';
            const rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
            rows.sort(function (a, b) {
                let av = a.children[idx]?.dataset.val ?? a.children[idx]?.textContent.trim() ?? '';
                let bv = b.children[idx]?.dataset.val ?? b.children[idx]?.textContent.trim() ?? '';
                if (type === 'num') { return ((parseFloat(av) || 0) - (parseFloat(bv) || 0)) * dir; }
                return String(av).localeCompare(String(bv), 'es') * dir;
            });
            rows.forEach(r => tbody.appendChild(r));
            renumber(table);
        });
    });

    // Numeración inicial
    document.querySelectorAll('table.ftable').forEach(t => renumber(t));
})();
</script>
