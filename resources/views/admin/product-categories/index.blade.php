@extends('layouts.admin')

@section('title', 'Kategori Produk')

@section('content')

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h1 class="h4 fw-bold text-dark mb-1">Kategori Produk</h1>
      <p class="small text-muted mb-0">Kelompok produk di katalog, mis. Shared Hosting, WordPress Hosting, VPS.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-box" style="font-size:11px"></i> Lihat Produk
      </a>
      <a href="{{ route('admin.product-categories.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus" style="font-size:11px"></i> Tambah Kategori
      </a>
    </div>
  </div>

  <p class="text-muted mb-3" style="font-size:12px"><i class="fa-solid fa-circle-info"></i> Seret ikon <i class="fa-solid fa-grip-vertical"></i> atau pakai tombol panah untuk menyusun urutan grup. Urutan ini dipakai di beranda, katalog publik, dan halaman client; tersimpan otomatis.</p>

  <div class="card border rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="small text-uppercase text-muted" style="background:#f8fafc">
            <th class="ps-3 pe-0 py-3" style="width:64px">Urut</th>
            <th class="px-4 py-3">Nama</th>
            <th class="py-3">Slug</th>
            <th class="text-center py-3">Produk</th>
            <th class="py-3">Status</th>
            <th class="text-end px-4 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody data-sortable data-reorder-url="{{ route('admin.product-categories.reorder') }}">
          @forelse ($categories as $category)
            <tr data-id="{{ $category->id }}">
              <td class="ps-3 pe-0 py-3" style="width:64px;white-space:nowrap">
                <span class="drag-handle text-muted" title="Seret untuk mengurutkan" style="cursor:grab;padding:4px 6px"><i class="fa-solid fa-grip-vertical" style="font-size:12px"></i></span>
                <button type="button" class="btn-move btn btn-link p-0 text-muted" data-dir="up" title="Naik"><i class="fa-solid fa-chevron-up" style="font-size:10px"></i></button>
                <button type="button" class="btn-move btn btn-link p-0 text-muted" data-dir="down" title="Turun"><i class="fa-solid fa-chevron-down" style="font-size:10px"></i></button>
              </td>
              <td class="px-4 py-3 fw-medium text-dark">
                @if ($category->icon)<i class="fa-solid {{ $category->icon }} text-muted me-1"></i>@endif
                {{ $category->name }}
              </td>
              <td class="text-muted py-3" style="font-size:12px">
                /store/{{ $category->slug }}
                <span class="badge {{ ($category->type ?? 'hosting') === 'vps' ? 'badge-soft-success' : 'badge-soft-secondary' }} ms-1" style="font-size:9px">
                  {{ $category->typeName() }}
                </span>
              </td>
              <td class="text-center py-3"><a href="{{ route('admin.products.index', ['category_id' => $category->id]) }}" class="text-muted">{{ $category->products_count }}</a></td>
              <td class="py-3">
                <span class="badge {{ $category->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </td>
              <td class="text-end px-4 py-3">
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <form method="POST" action="{{ route('admin.product-categories.status', $category) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="{{ $category->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                      <i class="fa-solid {{ $category->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}" style="font-size:12px"></i>
                    </button>
                  </form>
                  <a href="{{ route('admin.product-categories.edit', $category) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="Edit">
                    <i class="fa-regular fa-pen-to-square" style="font-size:12px"></i>
                  </a>
                  <form method="POST" action="{{ route('admin.product-categories.destroy', $category) }}"
                        data-confirm="Hapus kategori {{ $category->name }}?" data-confirm-title="Hapus Kategori"
                        data-confirm-style="danger" data-confirm-label="Ya, Hapus">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="Hapus">
                      <i class="fa-regular fa-trash-can" style="font-size:12px"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr class="no-sort"><td colspan="6" class="text-center text-muted py-5">Belum ada kategori produk.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @include('admin.partials.sortable')
@endsection
