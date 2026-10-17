<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('used')->default(0);
            $table->string('name', 255);
            $table->string('code', 255);
            $table->decimal('discount', 28, 16)->default(0);
            $table->decimal('min_order_amount', 28, 16)->default(0);
            $table->boolean('type');
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
