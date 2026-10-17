<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('frontends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('data_keys', 40)->nullable();
            $table->longText('data_values')->nullable();
            $table->longText('seo_content')->nullable();
            $table->string('tempname', 40)->nullable();
            $table->string('slug', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('frontends');
    }
};
