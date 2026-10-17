<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Jenis produk tingkat teratas (Hosting, VPS, Email, dst.), dibuat manual
 * oleh admin. `kind` = perilaku teknis ('hosting' | 'vps'); lihat migrasi.
 */
class ProductType extends Model
{
    protected $fillable = ['name', 'slug', 'kind', 'description', 'icon', 'is_active', 'sort_order'];

    public const KINDS = [
        'hosting' => 'Hosting (cPanel/WHM atau manual)',
        'vps'     => 'VPS / Cloud Server',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (ProductType $type) {
            $type->slug = blank($type->slug)
                ? static::uniqueSlug($type->name, $type->id)
                : Str::slug($type->slug);
        });

        // Perilaku jenis mengalir ke grup-grupnya (product_groups.type) supaya
        // semua logika lama yang membaca kolom itu tetap benar.
        static::updated(function (ProductType $type) {
            if ($type->wasChanged('kind')) {
                $type->groups()->update(['type' => $type->kind]);
            }
        });
    }

    public static function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'jenis';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function groups(): HasMany
    {
        return $this->hasMany(ProductGroup::class, 'product_type_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Halaman publik jenis ini. Dua jenis bawaan memakai halaman khususnya
     * (/hosting, /vps); jenis buatan admin memakai /layanan/{slug}.
     */
    public function publicUrl(): string
    {
        return match (true) {
            $this->slug === 'hosting' => route('catalog.index'),
            $this->slug === 'vps'     => route('catalog.vps'),
            default                   => route('catalog.type', [$this->slug]),
        };
    }
}
