<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('category_id')->default(0);
            $table->unsignedInteger('server_group_id')->default(0);
            $table->unsignedInteger('server_id')->default(0);
            $table->string('package_name', 255)->nullable();
            $table->boolean('product_type')->default(1);
            $table->boolean('payment_type')->default(1);
            $table->boolean('module_option')->default(3);
            $table->string('name', 255);
            $table->string('slug', 255);
            $table->text('description')->nullable();
            $table->boolean('welcome_email')->default(0);
            $table->boolean('domain_register')->default(0);
            $table->boolean('stock_control')->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
};
