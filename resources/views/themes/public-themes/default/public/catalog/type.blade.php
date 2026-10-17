@extends('public.layout')

@php
  $seoTitle = $type->name;
  $seoDescription = $type->description ?: "Pilihan layanan {$type->name} — aktif cepat, dukungan 24/7.";
@endphp

@section('content')

  <nav class="text-muted mb-3" style="font-size:12px">
    <a href="{{ route('catalog.index') }}" class="text-decoration-none text-muted">Katalog</a> / {{ $type->name }}
  </nav>

  <div class="mb-4">
    <h1 class="fw-bold text-dark mb-0" style="font-size:1.6rem">{{ $type->name }}</h1>
    @if ($type->description)
      <p class="text-muted mt-1 mb-0">{{ $type->description }}</p>
    @endif
  </div>

  @forelse ($categories as $cat)
    <section class="mb-5">
      <div class="d-flex align-items-end justify-content-between mb-3 flex-wrap gap-2">
        <div>
          <h2 class="fw-semibold text-dark mb-0" style="font-size:1.15rem">{{ $cat->name }}</h2>
          @if ($cat->description)
            <p class="text-muted mt-1 mb-0" style="font-size:13px">{{ $cat->description }}</p>
          @endif
        </div>
        <a href="{{ $cat->publicUrl() }}" class="text-decoration-none small">Lihat semua <i class="fa-solid fa-arrow-right" style="font-size:10px"></i></a>
      </div>

      <div class="row g-3">
        @foreach ($cat->products as $product)
          <div class="col-sm-6 col-lg-4">
            @include('public.catalog._product-card', ['product' => $product])
          </div>
        @endforeach
      </div>
    </section>
  @empty
    <div class="card-public p-5 text-center text-muted" style="font-size:14px">Belum ada produk di jenis ini.</div>
  @endforelse

@endsection
