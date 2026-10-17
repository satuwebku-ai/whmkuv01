<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('kind', ['hosting', 'vps'])->default('hosting');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('product_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->foreignId('product_type_id')->nullable()
                ->constrained('product_types')->nullOnDelete();
            // hosting = produk hosting biasa, vps = produk VPS/cloud --
            // dipakai form Tambah Produk untuk menyesuaikan isian & menyaring
            // pilihan server.
            $table->enum('type', ['hosting', 'vps'])->default('hosting');
            $table->text('description')->nullable();
            $table->string('icon')->nullable(); // nama ikon Font Awesome, mis. "fa-server"
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Jenis produk bawaan. Kategori yang sudah ada dipasangkan
        // berdasarkan nilai type; pada instalasi baru tabel kategori kosong.
        $now = now();
        $hostingId = DB::table('product_types')->insertGetId([
            'name' => 'Hosting',
            'slug' => 'hosting',
            'kind' => 'hosting',
            'icon' => 'fa-server',
            'is_active' => true,
            'sort_order' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $vpsId = DB::table('product_types')->insertGetId([
            'name' => 'VPS',
            'slug' => 'vps',
            'kind' => 'vps',
            'icon' => 'fa-cloud',
            'is_active' => true,
            'sort_order' => 2,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('product_groups')
            ->where('type', 'vps')
            ->update(['product_type_id' => $vpsId]);
        DB::table('product_groups')
            ->where(fn ($query) => $query->where('type', '!=', 'vps')->orWhereNull('type'))
            ->update(['product_type_id' => $hostingId]);
    }

    public function down(): void
    {
        Schema::dropIfExists('product_groups');
        Schema::dropIfExists('product_types');
    }
};
