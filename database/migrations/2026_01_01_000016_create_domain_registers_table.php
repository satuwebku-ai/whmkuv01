<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domain_registers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 40);
            $table->string('alias', 40);
            $table->string('ns1', 255)->nullable();
            $table->string('ns2', 255)->nullable();
            $table->string('ns3', 255)->nullable();
            $table->string('ns4', 255)->nullable();
            $table->text('params');
            $table->boolean('test_mode')->default(0);
            $table->boolean('default')->default(0);
            $table->boolean('status')->default(1);
            $table->boolean('setup_done')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_registers');
    }
};
