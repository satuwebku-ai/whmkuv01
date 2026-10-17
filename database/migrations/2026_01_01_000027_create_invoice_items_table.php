<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('invoice_id')->default(0);
            $table->unsignedInteger('user_id')->default(0);
            $table->unsignedInteger('hosting_id')->default(0);
            $table->unsignedInteger('domain_id')->default(0);
            $table->boolean('item_type')->default(0);
            $table->text('description');
            $table->decimal('amount', 28, 8)->default(0);
            $table->decimal('tax', 5, 2)->default(0);
            $table->integer('reg_period')->default(0);
            $table->string('trx_type', 40)->nullable();
            $table->decimal('recurring_amount', 28, 16)->default(0);
            $table->string('next_due_date', 40)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice_items');
    }
};
