<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('firstname', 40)->nullable();
            $table->string('lastname', 40)->nullable();
            $table->string('username', 40)->nullable();
            $table->string('email', 40);
            $table->string('dial_code', 40)->nullable();
            $table->string('country_code', 40)->nullable();
            $table->string('mobile', 40)->nullable();
            $table->unsignedInteger('ref_by')->default(0);
            $table->decimal('balance', 28, 8)->default(0);
            $table->string('password', 255);
            $table->string('country_name', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state', 255)->nullable();
            $table->string('zip', 255)->nullable();
            $table->text('address')->nullable();
            $table->boolean('status')->default(1);
            $table->text('kyc_data')->nullable();
            $table->string('kyc_rejection_reason', 255)->nullable();
            $table->boolean('kv')->default(0);
            $table->boolean('ev')->default(0);
            $table->boolean('sv')->default(0);
            $table->boolean('profile_complete')->default(0);
            $table->string('ver_code', 40)->nullable();
            $table->dateTime('ver_code_send_at')->nullable();
            $table->boolean('ts')->default(0);
            $table->boolean('tv')->default(1);
            $table->string('tsc', 255)->nullable();
            $table->string('ban_reason', 255)->nullable();
            $table->string('remember_token', 255)->nullable();
            $table->string('provider', 40)->nullable();
            $table->string('provider_id', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['username', 'email']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};
