<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('domain_pricings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('domain_id')->default(0);
            $table->decimal('one_year_price', 28, 16)->default(0);
            $table->decimal('one_year_id_protection', 28, 16)->default(0);
            $table->decimal('one_year_renew', 28, 16)->default(0);
            $table->decimal('two_year_price', 28, 16)->default(0);
            $table->decimal('two_year_id_protection', 28, 16)->default(0);
            $table->decimal('two_year_renew', 28, 16)->default(0);
            $table->decimal('three_year_price', 28, 16)->default(0);
            $table->decimal('three_year_id_protection', 28, 16)->default(0);
            $table->decimal('three_year_renew', 28, 16)->default(0);
            $table->decimal('four_year_price', 28, 16)->default(0);
            $table->decimal('four_year_id_protection', 28, 16)->default(0);
            $table->decimal('four_year_renew', 28, 16)->default(0);
            $table->decimal('five_year_price', 28, 16)->default(0);
            $table->decimal('five_year_id_protection', 28, 16)->default(0);
            $table->decimal('five_year_renew', 28, 16)->default(0);
            $table->decimal('six_year_price', 28, 16)->default(0);
            $table->decimal('six_year_id_protection', 28, 16)->default(0);
            $table->decimal('six_year_renew', 28, 16)->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('domain_pricings');
    }
};
