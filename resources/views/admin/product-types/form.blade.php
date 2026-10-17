@extends('layouts.admin')

@section('title', $type->exists ? 'Edit Jenis Produk' : 'Tambah Jenis Produk')

@section('content')

  <h1 class="h4 fw-bold text-dark mb-4">{{ $type->exists ? 'Edit Jenis Produk' : 'Tambah Jenis Produk' }}</h1>

  <form method="POST" action="{{ $type->exists ? route('admin.product-types.update', $type) : route('admin.product-types.store') }}" class="card border rounded-4 p-4" style="max-width:42rem">
    @csrf
    @if ($type->exists) @method('PUT') @endif

    <div class="mb-3">
      <label class="form-label small fw-medium text-dark">Nama Jenis</label>
      <input type="text" name="name" id="nameInput" value="{{ old('name', $type->name) }}" class="form-control form-control-sm" required placeholder="Email Hosting">
      @error('name') <p class="text-danger mt-1 mb-0" style="font-size:12px">{{ $message }}</p> @enderror
    </div>

    <div class="mb-3">
      <label class="form-label small fw-medium text-dark">Slug URL</label>
      <input type="text" name="slug" id="slugInput" value="{{ old('slug', $type->slug) }}" class="form-control form-control-sm" placeholder="otomatis dari nama">
      @error('slug') <p class="text-danger mt-1 mb-0" style="font-size:12px">{{ $message }}</p> @enderror
      <p class="text-muted mt-1 mb-0" style="font-size:11px">Halaman publik jenis ini: <code>/layanan/slug</code>. Jenis bawaan "hosting" dan "vps" tetap memakai <code>/hosting</code> dan <code>/vps</code>.</p>
    </div>

    <div class="mb-3">
      <label class="form-label small fw-medium text-dark">Perilaku Teknis</label>
      <select name="kind" class="form-select form-select-sm">
        @foreach (\App\Models\ProductType::KINDS as $k => $label)
          <option value="{{ $k }}" @selected(old('kind', $type->kind ?? 'hosting') === $k)>{{ $label }}</option>
        @endforeach
      </select>
      @error('kind') <p class="text-danger mt-1 mb-0" style="font-size:12px">{{ $message }}</p> @enderror
      <p class="text-muted mt-1 mb-0" style="font-size:11px">
        Menentukan isian produk, server yang boleh dipilih, dan provisioning otomatis. Pilih "Hosting" untuk layanan lain (email, SSL, jasa) yang diproses manual atau lewat cPanel.
        @if ($type->exists && $type->groups()->exists())
          Perilaku hanya bisa diubah jika semua produk di jenis ini cocok dengan perilaku barunya.
        @endif
      </p>
    </div>

    <div class="mb-3">
      <label class="form-label small fw-medium text-dark">Deskripsi Singkat</label>
      <textarea name="description" rows="2" maxlength="500" class="form-control form-control-sm" placeholder="Tampil di halaman jenis">{{ old('description', $type->description) }}</textarea>
    </div>

    <div class="mb-3">
      <label class="form-label small fw-medium text-dark">Ikon Font Awesome <span class="text-muted fw-normal">(opsional)</span></label>
      <input type="text" name="icon" value="{{ old('icon', $type->icon) }}" class="form-control form-control-sm" placeholder="fa-server">
    </div>

    <label class="d-flex align-items-center gap-2 small text-dark mb-3">
      <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $type->is_active ?? true)) class="form-check-input" style="margin-top:0">
      Aktif (tampil di katalog publik)
    </label>

    <div class="d-flex align-items-center gap-2 pt-2 border-top">
      <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="fa-solid fa-check" style="font-size:11px"></i> Simpan</button>
      <a href="{{ route('admin.product-types.index') }}" class="btn btn-outline-secondary btn-sm mt-2">Batal</a>
    </div>
  </form>

  <script @nonce>
    (function () {
      const name = document.getElementById('nameInput');
      const slug = document.getElementById('slugInput');
      const slugify = (s) => s.toLowerCase().trim().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
      let touched = slug.value.length > 0;
      slug.addEventListener('input', () => { touched = true; });
      name.addEventListener('input', () => { if (!touched) slug.value = slugify(name.value); });
    })();
  </script>

@endsection
