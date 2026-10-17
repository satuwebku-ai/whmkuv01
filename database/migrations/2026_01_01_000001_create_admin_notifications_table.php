<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('api_response')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->text('title')->nullable();
            $table->boolean('is_read')->default(0);
            $table->text('click_url')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('admin_notifications');
    }
};
