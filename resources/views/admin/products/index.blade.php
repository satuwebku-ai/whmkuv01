@extends('layouts.admin')
@section('title', 'Produk')

@section('content')
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h1 class="h4 fw-bold text-dark mb-1">Produk</h1>
      <p class="small text-muted mb-0">Katalog paket hosting/layanan yang dijual di halaman publik.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="{{ route('admin.product-categories.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-folder" style="font-size:11px"></i> Kategori</a>
      <a href="{{ route('admin.addons.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-puzzle-piece" style="font-size:11px"></i> Addons</a>
      <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus" style="font-size:11px"></i> Tambah Produk</a>
    </div>
  </div>

  {{-- Filter per Jenis (Hosting, VPS, dll. -- dibuat manual di Jenis Produk) --}}
  @php $j = request('jenis'); @endphp
  <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
    <a href="{{ route('admin.products.index') }}" class="px-3 py-2 rounded-pill small fw-medium text-decoration-none {{ ! $j ? 'text-white' : 'text-muted' }}" style="{{ ! $j ? 'background:#4f46e5' : 'background:#f1f5f9' }}">
      Semua ({{ $counts['all'] }})
    </a>
    @foreach ($types as $t)
      <a href="{{ route('admin.products.index', ['jenis' => $t->id]) }}" class="px-3 py-2 rounded-pill small fw-medium text-decoration-none {{ (string) $j === (string) $t->id ? 'text-white' : 'text-muted' }}" style="{{ (string) $j === (string) $t->id ? 'background:#4f46e5' : 'background:#f1f5f9' }}">
        @if ($t->icon)<i class="fa-solid {{ $t->icon }}" style="font-size:10px"></i>@endif
        {{ $t->name }} ({{ $typeCounts[$t->id] ?? 0 }})
      </a>
    @endforeach
    <a href="{{ route('admin.product-types.index') }}" class="small text-decoration-none text-muted ms-1"><i class="fa-solid fa-gear" style="font-size:10px"></i> Kelola jenis</a>
  </div>

  <div class="card border rounded-4 overflow-hidden mb-3">
    <form method="GET" class="px-4 py-3 border-bottom d-flex flex-wrap align-items-center gap-2">
      <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama produk..." class="form-control form-control-sm" style="max-width:16rem;flex:1 1 180px">
      <select name="category_id" class="form-select" style="padding:.25rem .6rem;font-size:.875rem;border-radius:.375rem;max-width:12rem" data-auto-submit>
        <option value="">Semua Kategori</option>
        @foreach ($categories as $cat)
          <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
        @endforeach
      </select>
      <button type="submit" class="btn btn-outline-secondary btn-sm" style="width:fit-content">Cari</button>
      @if (request('search') || request('category_id') || request('jenis'))
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary btn-sm" style="width:fit-content">Reset</a>
      @endif
    </form>
  </div>

  @if ($canReorder)
    <p class="text-muted mb-3" style="font-size:12px"><i class="fa-solid fa-circle-info"></i> Urutan grup dan produk di sini sama persis dengan yang tampil di katalog publik dan halaman client. Seret ikon <i class="fa-solid fa-grip-vertical"></i> atau pakai tombol panah untuk menyusun ulang; tersimpan otomatis. Urutan grup diatur di <a href="{{ route('admin.product-categories.index') }}" class="text-accent">Kategori Produk</a>.</p>
  @endif

  @forelse ($groups as $group)
    <div class="card border rounded-4 overflow-hidden mb-3">
      <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#f8fafc">
        <div class="d-flex align-items-center gap-2">
          @if ($group->icon)<i class="fa-solid {{ $group->icon }} text-muted"></i>@endif
          <span class="fw-bold text-dark">{{ $group->name }}</span>
          <span class="badge {{ ($group->type ?? 'hosting') === 'vps' ? 'badge-soft-success' : 'badge-soft-secondary' }}" style="font-size:9px">{{ $group->typeName() }}</span>
          @unless ($group->is_active)<span class="badge badge-soft-secondary" style="font-size:9px">Grup nonaktif</span>@endunless
          <span class="text-muted" style="font-size:12px">{{ $group->products->count() }} produk</span>
        </div>
        <a href="{{ route('admin.products.create', ['category_id' => $group->id]) }}" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-plus" style="font-size:10px"></i> Produk di grup ini</a>
      </div>

      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead>
            <tr class="small text-uppercase text-muted">
              @if ($canReorder)<th class="ps-3 pe-0 py-2" style="width:64px">Urut</th>@endif
              <th class="px-4 py-2">Produk</th>
              <th class="text-end py-2">Mulai Dari</th>
              <th class="py-2">Domain</th>
              <th class="py-2">Status</th>
              <th class="text-end px-4 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody @if ($canReorder) data-sortable data-reorder-url="{{ route('admin.products.reorder') }}" @endif>
            @forelse ($group->products as $product)
            <tr data-id="{{ $product->id }}">
              @if ($canReorder)
                <td class="ps-3 pe-0 py-3" style="width:64px;white-space:nowrap">
                  <span class="drag-handle text-muted" title="Seret untuk mengurutkan" style="cursor:grab;padding:4px 6px"><i class="fa-solid fa-grip-vertical" style="font-size:12px"></i></span>
                  <button type="button" class="btn-move btn btn-link p-0 text-muted" data-dir="up" title="Naik"><i class="fa-solid fa-chevron-up" style="font-size:10px"></i></button>
                  <button type="button" class="btn-move btn btn-link p-0 text-muted" data-dir="down" title="Turun"><i class="fa-solid fa-chevron-down" style="font-size:10px"></i></button>
                </td>
              @endif
              <td class="px-4 py-3">
                @php
                  $isVps = $product->server_id && $cloudServerIds->contains($product->server_id);
                  $spec = $isVps ? json_decode((string) $product->panel_package, true) : null;
                @endphp
                <p class="fw-medium text-dark mb-0">
                  {{ $product->name }}
                  @if ($isVps)
                    <span class="badge badge-soft-success ms-1" style="font-size:9px"><i class="fa-solid fa-cloud"></i> VPS</span>
                  @endif
                  @if ($product->is_featured)
                    <i class="fa-solid fa-star text-warning ms-1" style="font-size:10px" title="Unggulan"></i>
                  @endif
                </p>
                @if ($isVps && is_array($spec) && isset($spec['vcpu']))
                  <p class="text-muted mb-0" style="font-size:11px">
                    {{ $spec['vcpu'] }} vCPU · {{ $spec['ram'] }} MB · {{ $spec['disk'] }} GB · {{ $spec['os_name'] ?? '' }}
                  </p>
                @elseif ($isVps)
                  <p class="mb-0" style="font-size:11px;color:#b45309">
                    <i class="fa-solid fa-triangle-exclamation"></i> Spesifikasi VPS belum diisi
                  </p>
                @elseif ($product->tagline)
                  <p class="text-muted mb-0" style="font-size:12px">{{ $product->tagline }}</p>
                @endif
              </td>
              <td class="text-muted py-3">{{ $product->category->name ?? '—' }}</td>
              <td class="text-end text-dark py-3">
                @if ($product->starting_price !== null)
                  Rp {{ number_format($product->starting_price, 0, ',', '.') }}
                @else
                  <span class="text-danger" style="font-size:12px">Belum ada harga</span>
                @endif
              </td>
              <td class="text-muted text-capitalize py-3" style="font-size:12px">{{ $product->domain_option }}</td>
              <td class="py-3">
                <span class="badge {{ $product->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}</span>
              </td>
              <td class="text-end px-4 py-3">
                <div class="d-flex align-items-center justify-content-end gap-2">
                  <form method="POST" action="{{ route('admin.product.status') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                      <i class="fa-solid {{ $product->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}" style="font-size:12px"></i>
                    </button>
                  </form>
                  @if ($product->is_active && $product->category)
                    <a href="{{ $product->category->productUrl($product) }}" target="_blank" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="Lihat di katalog">
                      <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:12px"></i>
                    </a>
                  @endif
                  <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-outline-secondary btn-sm d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;padding:0" title="Edit">
                    <i class="fa-regular fa-pen-to-square" style="font-size:12px"></i>
                  </a>
                  <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                        data-confirm="Hapus produk {{ $product->name }}?" data-confirm-title="Hapus Produk"
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
              <tr class="no-sort"><td colspan="6" class="text-center text-muted py-4" style="font-size:13px">Belum ada produk di grup ini.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  @empty
    <div class="card border rounded-4 p-5 text-center text-muted">Belum ada produk. Buat kategori dulu kalau belum ada.</div>
  @endforelse

  @include('admin.partials.sortable')
@endsection
