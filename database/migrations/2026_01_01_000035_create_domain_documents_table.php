<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TLD Indonesia tertentu (.co.id, .ac.id, dst) mewajibkan dokumen
     * identitas/legalitas diverifikasi PANDI sebelum domain aktif — di
     * luar API Liqu.id sama sekali (dikonfirmasi: tidak ada endpoint
     * upload dokumen di spesifikasi resmi mereka). Klien upload ke sini,
     * admin tinjau, baru diteruskan manual ke Liqu.id.
     */
    public function up(): void
    {
        Schema::create('domain_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            // Kolom relasi; foreign key ditambahkan setelah document_requirements dibuat.
            $table->foreignId('document_requirement_id')->nullable();
            $table->string('file_path');
            $table->string('original_name');
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domain_documents');
    }
};
