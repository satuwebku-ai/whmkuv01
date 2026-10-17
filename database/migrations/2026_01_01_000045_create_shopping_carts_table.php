<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('domain_register_id')->default(0);
            $table->unsignedBigInteger('user_id')->default(0);
            $table->unsignedInteger('domain_id')->default(0);
            $table->unsignedInteger('product_id')->default(0);
            $table->unsignedInteger('domain_setup_id')->default(0);
            $table->unsignedInteger('coupon_id')->default(0);
            $table->tinyInteger('type')->default(1);
            $table->tinyInteger('id_protection')->default(0);
            $table->integer('reg_period')->default(0);
            $table->boolean('billing_cycle')->nullable();
            $table->string('domain', 255)->nullable();
            $table->string('hostname', 40)->nullable();
            $table->string('ns1', 255)->nullable();
            $table->string('ns2', 255)->nullable();
            $table->string('ns3', 255)->nullable();
            $table->string('ns4', 255)->nullable();
            $table->string('password', 255)->nullable();
            $table->decimal('price', 28, 16)->default(0);
            $table->decimal('setup_fee', 28, 16)->default(0);
            $table->boolean('discount_applied')->default(0);
            $table->tinyInteger('coupon_type')->default(0);
            $table->decimal('coupon_discount', 28, 8)->default(0);
            $table->decimal('discount', 28, 16)->default(0);
            $table->decimal('total', 28, 16)->default(0);
            $table->decimal('after_discount', 28, 16)->default(0);
            $table->string('config_options', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopping_carts');
    }
};
