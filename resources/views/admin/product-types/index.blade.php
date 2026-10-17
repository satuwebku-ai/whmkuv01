@extends('layouts.admin')

@section('title', 'Jenis Produk')

@section('content')

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h1 class="h4 fw-bold text-dark mb-1">Jenis Produk</h1>
      <p class="small text-muted mb-0">Tingkat teratas katalog, mis. Hosting, VPS, Email Hosting. Susunannya: Jenis → Kategori → Produk.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-folder" style="font-size:11px"></i> Kategori
      </a>
      <a href="{{ route('admin.product-types.create') }}" class="btn btn-primary btn-sm">
        <i class="fa-solid fa-plus" style="font-size:11px"></i> Tambah Jenis
      </a>
    </div>
  </div>

  <p class="text-muted mb-3" style="font-size:12px"><i class="fa-solid fa-circle-info"></i> Seret ikon <i class="fa-solid fa-grip-vertical"></i> atau pakai tombol panah untuk menyusun urutan jenis; tersimpan otomatis.</p>

  <div class="card border rounded-4 overflow-hidden">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr class="small text-uppercase text-muted" style="background:#f8fafc">
            <th class="ps-3 pe-0 py-3" style="width:64px">Urut</th>
            <th class="px-4 py-3">Nama</th>
            <th class="py-3">Halaman Publik</th>
            <th class="py-3">Perilaku</th>
            <th class="text-center py-3">Kategori</th>
            <th class="py-3">Status</th>
            <th class="text-end px-4 py-3">Aksi</th>
          </tr>
        </thead>
        <tbody data-sortable data-reorder-url="{{ route('admin.product-types.reorder') }}">
          @forelse ($types as $type)
            <tr data-id="{{ $type->id }}">
              <td class="ps-3 pe-0 py-3" style="width:64px;white-space:nowrap">
                <span class="drag-handle text-muted" title="Seret untuk mengurutkan" style="cursor:grab;padding:4px 6px"><i class="fa-solid fa-grip-vertical" style="font-size:12px"></i></span>
                <button type="button" class="btn-move btn btn-link p-0 text-muted" data-dir="up" title="Naik"><i class="fa-solid fa-chevron-up" style="font-size:10px"></i></button>
                <button type="button" class="btn-move btn btn-link p-0 text-muted" data-dir="down" title="Turun"><i class="fa-solid fa-chevron-down" style="font-size:10px"></i></button>
              </td>
              <td class="px-4 py-3 fw-medium text-dark">
                @if ($type->icon)<i class="fa-solid {{ $type->icon }} text-muted me-1"></i>@endif
                {{ $type->name }}
              </td>
              <td class="text-muted py-3" style="font-size:12px">{{ parse_url($type->publicUrl(), PHP_URL_PATH) }}</td>
              <td class="py-3">
                <span class="badge {{ $type->kind === 'vps' ? 'badge-soft-success' : 'badge-soft-secondary' }}" style="font-size:10px">{{ $type->kind === 'vps' ? 'VPS / Cloud' : 'Hosting' }}</span>
              </td>
              <td class="text-center py-3"><a href="{{ route('admin.product-categories.index') }}" class="text-muted">{{ $type->groups_count }}</a></td>
              <td class="py-3">
                <span class="badge {{ $type->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $type->is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </td>
              <td class="text-end px-4 py-3">
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <form method="POST" action="{{ route('admin.product-types.status', $type) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="{{ $type->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                      <i class="fa-solid {{ $type->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}" style="font-size:12px"></i>
                    </button>
                  </form>
                  <a href="{{ route('admin.product-types.edit', $type) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="Edit">
                    <i class="fa-regular fa-pen-to-square" style="font-size:12px"></i>
                  </a>
                  <form method="POST" action="{{ route('admin.product-types.destroy', $type) }}"
                        data-confirm="Hapus jenis {{ $type->name }}?" data-confirm-title="Hapus Jenis"
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
            <tr class="no-sort"><td colspan="7" class="text-center text-muted py-5">Belum ada jenis produk.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @include('admin.partials.sortable')
@endsection
