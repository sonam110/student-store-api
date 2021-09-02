<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDeviceInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_device_infos', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('fcm_token',255)->nullable();
            $table->string('device_uuid',100)->nullable();
            $table->string('platform',100)->nullable();
            $table->string('model',100)->nullable();
            $table->string('os_version',100)->nullable();
            $table->string('manufacturer',100)->nullable();
            $table->string('serial_number',100)->nullable();
            $table->string('system_ip_address',100)->nullable();
            $table->boolean('status')->default(false)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_device_infos');
    }
}
