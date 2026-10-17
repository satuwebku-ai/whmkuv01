<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 40)->nullable();
            $table->string('alias', 40)->nullable();
            $table->text('action')->nullable();
            $table->string('url', 255)->nullable();
            $table->integer('cron_schedule_id')->default(0);
            $table->dateTime('next_run')->nullable();
            $table->dateTime('last_run')->nullable();
            $table->boolean('is_running')->default(1);
            $table->boolean('is_default')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cron_jobs');
    }
};
