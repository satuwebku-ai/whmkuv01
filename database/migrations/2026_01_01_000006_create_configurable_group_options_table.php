<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('configurable_group_options', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('option_type')->default(0);
            $table->unsignedInteger('configurable_group_id')->default(0);
            $table->string('name', 255);
            $table->boolean('order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('configurable_group_options');
    }
};
