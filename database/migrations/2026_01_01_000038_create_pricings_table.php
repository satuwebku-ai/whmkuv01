<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pricings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 255);
            $table->unsignedInteger('product_id')->default(0);
            $table->unsignedInteger('configurable_group_sub_option_id')->default(0);
            $table->decimal('monthly_setup_fee', 28, 8)->default(0);
            $table->decimal('quarterly_setup_fee', 28, 8)->default(0);
            $table->decimal('semi_annually_setup_fee', 28, 8)->default(0);
            $table->decimal('annually_setup_fee', 28, 8)->default(0);
            $table->decimal('biennially_setup_fee', 28, 8)->default(0);
            $table->decimal('triennially_setup_fee', 28, 8)->default(0);
            $table->decimal('monthly', 28, 8)->default(0);
            $table->decimal('quarterly', 28, 8)->default(0);
            $table->decimal('semi_annually', 28, 8)->default(0);
            $table->decimal('annually', 28, 8)->default(0);
            $table->decimal('biennially', 28, 8)->default(0);
            $table->decimal('triennially', 28, 8)->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pricings');
    }
};
