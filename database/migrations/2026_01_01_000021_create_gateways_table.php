<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gateways', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('form_id')->default(0);
            $table->integer('code')->nullable();
            $table->string('name', 40)->nullable();
            $table->string('alias', 40)->default('NULL');
            $table->string('image', 255)->nullable();
            $table->boolean('status')->default(1);
            $table->text('gateway_parameters')->nullable();
            $table->text('supported_currencies')->nullable();
            $table->boolean('crypto')->default(0);
            $table->text('extra')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gateways');
    }
};
