<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('role_id')->default(0);
            $table->string('name', 40)->nullable();
            $table->string('email', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->text('address')->nullable();
            $table->string('username', 40)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('password', 255);
            $table->boolean('status')->default(1);
            $table->string('remember_token', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['email', 'username']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
};
