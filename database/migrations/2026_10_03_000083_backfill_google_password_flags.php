<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Akun Google lama memakai password acak yang tidak diketahui klien.
        DB::table('clients')
            ->whereNotNull('google_id')
            ->update(['password_set_by_user' => false]);
    }

    public function down(): void
    {
        // Tidak bisa dibalik dengan aman tanpa mengetahui nilai sebelumnya.
    }
};
