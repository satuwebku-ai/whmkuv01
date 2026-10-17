<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ProductGroup;
use App\Models\ProductType;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        // Tanpa paginasi: jumlah grup kecil, dan urutan manual (drag-and-drop)
        // baru bermakna kalau seluruh grup tampil dalam satu daftar.
        $categories = ProductGroup::with('productType')->withCount('products')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.product-categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.product-categories.form', [
            'category' => new ProductGroup(),
            'types' => ProductType::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active', true);
        // Grup baru otomatis masuk paling bawah; urutannya diatur dari daftar.
        $data['sort_order'] = $data['sort_order'] ?? ((int) ProductGroup::max('sort_order') + 1);

        $category = ProductGroup::create($data);

        $this->audit('Kategori produk dibuat', "{$category->name} (jenis: {$category->typeName()})", 'info');

        return redirect()->route('admin.product-categories.index')->with('success', 'Kategori berhasil dibuat.');
    }

    public function edit(ProductGroup $productCategory): View
    {
        return view('admin.product-categories.form', [
            'category' => $productCategory,
            'types' => ProductType::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ProductGroup $productCategory): RedirectResponse
    {
        $data = $this->validated($request, $productCategory->id);
        $data['is_active'] = $request->boolean('is_active');

        // Mengubah jenis (hosting <-> vps) mengubah URL publik (/hosting/...
        // <-> /vps/...) DAN aturan server tiap produknya. Produk lama tidak
        // dicek ulang oleh ProductController, jadi tanpa guard ini produk
        // hosting bisa tiba-tiba "berubah" jadi produk VPS tanpa server
        // cloud (atau sebaliknya) dan nyasar di katalog yang salah.
        if ($data['type'] !== ($productCategory->type ?? 'hosting')) {
            $conflict = $this->typeChangeConflict($productCategory, $data['type']);

            if ($conflict) {
                return back()->withInput()->withErrors(['product_type_id' => $conflict]);
            }
        }

        $productCategory->fill($data);
        $changes = collect($productCategory->getDirty())
            ->map(fn ($new, $field) => "{$field}: " . json_encode($productCategory->getOriginal($field)) . ' → ' . json_encode($new))
            ->implode('; ');

        $productCategory->save();

        if ($changes !== '') {
            $this->audit('Kategori produk diubah', "{$productCategory->name} — {$changes}", 'info');
        }

        return redirect()->route('admin.product-categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Simpan urutan manual grup. Body: {"ids": [3, 1, 2]} sesuai urutan baru.
     */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:product_groups,id'],
        ])['ids'];

        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $i => $id) {
                ProductGroup::whereKey($id)->update(['sort_order' => $i + 1]);
            }
        });

        $this->audit('Urutan kategori produk diubah', count($ids) . ' kategori disusun ulang', 'info');

        return response()->json(['ok' => true]);
    }

    public function status(ProductGroup $productCategory): RedirectResponse
    {
        $productCategory->update(['is_active' => ! $productCategory->is_active]);

        $this->audit('Status kategori produk diubah', "{$productCategory->name} → " . ($productCategory->is_active ? 'aktif' : 'nonaktif'), 'info');

        return back()->with('success', "Kategori {$productCategory->name} berhasil " . ($productCategory->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    public function destroy(ProductGroup $productCategory): RedirectResponse
    {
        if ($productCategory->products()->exists()) {
            return back()->with('error', 'Kategori tidak bisa dihapus karena masih punya produk. Pindahkan atau hapus produknya dulu.');
        }

        $name = $productCategory->name;
        $productCategory->delete();

        $this->audit('Kategori produk dihapus', $name, 'warning');

        return redirect()->route('admin.product-categories.index')->with('success', 'Kategori berhasil dihapus.');
    }

    /**
     * Pesan error kalau perubahan jenis kategori bertabrakan dengan server
     * produk di dalamnya; null kalau aman. Aturannya sama dengan
     * ProductController::assertCategoryMatchesServer().
     */
    private function typeChangeConflict(ProductGroup $category, string $newType): ?string
    {
        $cloudIds = Server::cloud()->pluck('id');

        $bad = $category->products()
            ->when($newType === 'vps',
                fn ($q) => $q->where(fn ($w) => $w->whereNull('server_id')->orWhereNotIn('server_id', $cloudIds)),
                fn ($q) => $q->whereIn('server_id', $cloudIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($bad->isEmpty()) {
            return null;
        }

        $names = $bad->take(5)->pluck('name')->implode(', ') . ($bad->count() > 5 ? ', …' : '');
        $need = $newType === 'vps' ? 'belum memakai server VPS/cloud' : 'memakai server VPS/cloud';

        return "Jenis tidak bisa diubah ke " . strtoupper($newType) . " karena {$bad->count()} produk di kategori ini {$need}: {$names}. Pindahkan produk tersebut ke kategori lain atau sesuaikan servernya dulu.";
    }

    private function audit(string $title, string $detail, string $level): void
    {
        $who = auth('admin')->user()->name ?? 'admin';

        ActivityLog::record('service', $title, "{$detail}. Oleh {$who}.", null, $level);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'product_type_id' => ['required', 'exists:product_types,id'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:product_groups,slug' . ($ignoreId ? ",{$ignoreId}" : '')],
            'description' => ['nullable', 'string', 'max:500'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ]);

        // Perilaku teknis (hosting/vps) diwariskan dari Jenis yang dipilih.
        $data['type'] = ProductType::whereKey($data['product_type_id'])->value('kind') ?? 'hosting';

        return $data;
    }
}
