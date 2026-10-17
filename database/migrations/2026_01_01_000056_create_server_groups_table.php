<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->foreign('server_group_id')
                ->references('id')->on('server_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['server_group_id']);
        });

        Schema::dropIfExists('server_groups');
    }
};
