<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('server_group_id')->default(0);
            $table->string('name', 255);
            $table->string('type', 255);
            $table->string('hostname', 255);
            $table->string('protocol', 45)->nullable();
            $table->string('port', 45)->nullable();
            $table->string('host', 255)->nullable();
            $table->string('username', 255);
            $table->string('password', 255);
            $table->string('ip_address', 255)->nullable();
            $table->text('api_token')->nullable();
            $table->string('security_token', 255)->nullable();
            $table->string('ns1', 255);
            $table->string('ns1_ip', 255);
            $table->string('ns2', 255);
            $table->string('ns2_ip', 255);
            $table->string('ns3', 255)->nullable();
            $table->string('ns3_ip', 255)->nullable();
            $table->string('ns4', 255)->nullable();
            $table->string('ns4_ip', 255)->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('servers');
    }
};
