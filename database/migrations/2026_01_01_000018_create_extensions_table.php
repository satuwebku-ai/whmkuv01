<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('extensions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('act', 40)->nullable();
            $table->string('name', 40)->nullable();
            $table->text('description')->nullable();
            $table->string('image', 255)->nullable();
            $table->text('script')->nullable();
            $table->text('shortcode')->nullable();
            $table->text('support')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('extensions');
    }
};
