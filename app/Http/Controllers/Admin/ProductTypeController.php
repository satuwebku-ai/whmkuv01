<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ProductType;
use App\Models\Server;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Jenis Produk (Hosting, VPS, Email, dst.) -- tingkat teratas hierarki
 * Jenis -> Grup -> Produk. Dibuat manual oleh admin.
 */
class ProductTypeController extends Controller
{
    public function index(): View
    {
        $types = ProductType::withCount('groups')->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.product-types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.product-types.form', ['type' => new ProductType()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int) ProductType::max('sort_order') + 1;

        $type = ProductType::create($data);

        $this->audit('Jenis produk dibuat', "{$type->name} (perilaku: {$type->kind})", 'info');

        return redirect()->route('admin.product-types.index')->with('success', 'Jenis produk berhasil dibuat.');
    }

    public function edit(ProductType $productType): View
    {
        return view('admin.product-types.form', ['type' => $productType]);
    }

    public function update(Request $request, ProductType $productType): RedirectResponse
    {
        $data = $this->validated($request, $productType->id);
        $data['is_active'] = $request->boolean('is_active');

        // Mengubah perilaku (hosting <-> vps) mengubah aturan server semua
        // produk di jenis ini; tolak kalau ada produk yang tidak cocok --
        // aturannya sama dengan ProductCategoryController::typeChangeConflict().
        if ($data['kind'] !== $productType->kind) {
            $conflict = $this->kindChangeConflict($productType, $data['kind']);

            if ($conflict) {
                return back()->withInput()->withErrors(['kind' => $conflict]);
            }
        }

        $productType->fill($data);
        $changes = collect($productType->getDirty())
            ->map(fn ($new, $field) => "{$field}: " . json_encode($productType->getOriginal($field)) . ' → ' . json_encode($new))
            ->implode('; ');

        $productType->save();

        if ($changes !== '') {
            $this->audit('Jenis produk diubah', "{$productType->name} — {$changes}", 'info');
        }

        return redirect()->route('admin.product-types.index')->with('success', 'Jenis produk berhasil diperbarui.');
    }

    public function destroy(ProductType $productType): RedirectResponse
    {
        if ($productType->groups()->exists()) {
            return back()->with('error', 'Jenis tidak bisa dihapus karena masih punya kategori. Pindahkan atau hapus kategorinya dulu.');
        }

        $name = $productType->name;
        $productType->delete();

        $this->audit('Jenis produk dihapus', $name, 'warning');

        return redirect()->route('admin.product-types.index')->with('success', 'Jenis produk berhasil dihapus.');
    }

    public function status(ProductType $productType): RedirectResponse
    {
        $productType->update(['is_active' => ! $productType->is_active]);

        $this->audit('Status jenis produk diubah', "{$productType->name} → " . ($productType->is_active ? 'aktif' : 'nonaktif'), 'info');

        return back()->with('success', "Jenis {$productType->name} berhasil " . ($productType->is_active ? 'diaktifkan.' : 'dinonaktifkan.'));
    }

    /** Simpan urutan manual jenis. Body: {"ids": [2, 1, 3]}. */
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:product_types,id'],
        ])['ids'];

        DB::transaction(function () use ($ids) {
            foreach (array_values($ids) as $i => $id) {
                ProductType::whereKey($id)->update(['sort_order' => $i + 1]);
            }
        });

        return response()->json(['ok' => true]);
    }

    private function kindChangeConflict(ProductType $type, string $newKind): ?string
    {
        $cloudIds = Server::cloud()->pluck('id');

        $bad = \App\Models\Product::whereIn('product_category_id', $type->groups()->select('id'))
            ->when($newKind === 'vps',
                fn ($q) => $q->where(fn ($w) => $w->whereNull('server_id')->orWhereNotIn('server_id', $cloudIds)),
                fn ($q) => $q->whereIn('server_id', $cloudIds))
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($bad->isEmpty()) {
            return null;
        }

        $names = $bad->take(5)->pluck('name')->implode(', ') . ($bad->count() > 5 ? ', …' : '');
        $need = $newKind === 'vps' ? 'belum memakai server VPS/cloud' : 'memakai server VPS/cloud';

        return "Perilaku tidak bisa diubah ke " . strtoupper($newKind) . " karena {$bad->count()} produk di jenis ini {$need}: {$names}. Pindahkan produk tersebut dulu.";
    }

    private function audit(string $title, string $detail, string $level): void
    {
        $who = auth('admin')->user()->name ?? 'admin';

        ActivityLog::record('service', $title, "{$detail}. Oleh {$who}.", null, $level);
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:255', 'unique:product_types,slug' . ($ignoreId ? ",{$ignoreId}" : '')],
            'kind'        => ['required', 'in:' . implode(',', array_keys(ProductType::KINDS))],
            'description' => ['nullable', 'string', 'max:500'],
            'icon'        => ['nullable', 'string', 'max:50'],
            'is_active'   => ['nullable', 'boolean'],
        ]);
    }
}
