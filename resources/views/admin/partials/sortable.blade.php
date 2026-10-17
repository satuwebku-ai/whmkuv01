{{-- ══════════ Susunan manual (seret-urut) ══════════
     Dipakai daftar Kategori Produk dan daftar Produk. Setiap <tbody> yang
     punya atribut data-sortable + data-reorder-url jadi satu kelompok urut;
     baris <tr data-id> di dalamnya bisa diseret lewat .drag-handle atau
     digeser dengan tombol .btn-move (untuk layar sentuh, karena drag HTML5
     tidak jalan di mobile). Tiap perubahan langsung dikirim ke server
     sebagai {"ids":[...]} sesuai urutan baru di layar. --}}
<style>
  tr.is-dragging { opacity: .45; background: #eef2ff; }
  [data-sortable] tr[data-id] .btn-move { line-height: 1; padding: 2px 3px !important; }
</style>
<script @nonce>
  (function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    document.querySelectorAll('[data-sortable]').forEach(function (body) {
      const url = body.dataset.reorderUrl;
      let dragging = null;

      const rows = () => Array.from(body.querySelectorAll('tr[data-id]'));

      async function save() {
        const ids = rows().map(r => Number(r.dataset.id));
        try {
          const res = await fetch(url, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrf,
              'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ ids: ids }),
          });
          const data = await res.json().catch(() => ({}));
          if (!res.ok || data.ok === false) throw new Error(data.message || 'Gagal menyimpan urutan.');
          if (window.lumoraToast) lumoraToast('success', 'Urutan tersimpan.');
        } catch (e) {
          if (window.lumoraToast) lumoraToast('error', e.message || 'Gagal menyimpan urutan. Muat ulang halaman.');
        }
      }

      // Seret: baris hanya draggable selama pointer menekan handle-nya,
      // supaya teks/tombol lain di baris tetap bisa dipilih & diklik.
      body.addEventListener('mousedown', function (e) {
        const row = e.target.closest('tr[data-id]');
        if (row) row.draggable = !!e.target.closest('.drag-handle');
      });

      body.addEventListener('dragstart', function (e) {
        dragging = e.target.closest('tr[data-id]');
        if (!dragging) return;
        dragging.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', dragging.dataset.id);
      });

      body.addEventListener('dragover', function (e) {
        if (!dragging) return;
        e.preventDefault();
        const over = e.target.closest('tr[data-id]');
        if (!over || over === dragging) return;
        const box = over.getBoundingClientRect();
        const after = (e.clientY - box.top) > box.height / 2;
        body.insertBefore(dragging, after ? over.nextSibling : over);
      });

      body.addEventListener('dragend', function () {
        if (!dragging) return;
        dragging.classList.remove('is-dragging');
        dragging.draggable = false;
        dragging = null;
        save();
      });

      // Tombol naik/turun: tukar dengan tetangga langsung.
      body.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-move');
        if (!btn) return;
        const row = btn.closest('tr[data-id]');
        const list = rows();
        const i = list.indexOf(row);
        if (btn.dataset.dir === 'up' && i > 0) body.insertBefore(row, list[i - 1]);
        else if (btn.dataset.dir === 'down' && i < list.length - 1) body.insertBefore(list[i + 1], row);
        else return;
        save();
      });
    });
  })();
</script>
