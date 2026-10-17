<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('general_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->decimal('tax', 5, 2)->default(0);
            $table->string('site_name', 40)->nullable();
            $table->integer('invoice_start')->default(1);
            $table->integer('invoice_increment')->default(1);
            $table->boolean('deposit_module')->default(1);
            $table->boolean('auto_domain_register')->default(1);
            $table->string('last_cron', 40)->nullable();
            $table->string('available_version', 40)->default('1.0');
            $table->string('cur_text', 40)->nullable();
            $table->string('cur_sym', 40)->nullable();
            $table->string('email_from', 40)->nullable();
            $table->string('email_from_name', 255)->nullable();
            $table->text('email_template')->nullable();
            $table->string('sms_template', 255)->nullable();
            $table->string('sms_from', 255)->nullable();
            $table->string('push_title', 255)->nullable();
            $table->string('push_template', 255)->nullable();
            $table->string('base_color', 40)->nullable();
            $table->text('mail_config')->nullable();
            $table->text('sms_config')->nullable();
            $table->text('firebase_config')->nullable();
            $table->text('global_shortcodes')->nullable();
            $table->boolean('kv')->default(0);
            $table->boolean('ev')->default(0);
            $table->boolean('en')->default(0);
            $table->boolean('sv')->default(0);
            $table->boolean('sn')->default(0);
            $table->boolean('pn')->default(1);
            $table->boolean('force_ssl')->default(0);
            $table->boolean('maintenance_mode')->default(0);
            $table->boolean('secure_password')->default(0);
            $table->boolean('agree')->default(0);
            $table->boolean('multi_language')->default(1);
            $table->boolean('registration')->default(0);
            $table->string('active_template', 40)->nullable();
            $table->text('socialite_credentials')->nullable();
            $table->boolean('system_customized')->default(0);
            $table->integer('paginate_number')->default(0);
            $table->boolean('currency_format')->default(0);
            $table->text('config_progress')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_settings');
    }
};
