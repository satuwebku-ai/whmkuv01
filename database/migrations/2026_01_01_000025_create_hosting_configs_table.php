<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hosting_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('hosting_id')->default(0);
            $table->unsignedInteger('configurable_group_option_id')->default(0);
            $table->unsignedInteger('configurable_group_sub_option_id')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hosting_configs');
    }
};
