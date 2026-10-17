<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('support_attachments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('support_message_id')->default(0);
            $table->string('attachment', 255)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('support_attachments');
    }
};
