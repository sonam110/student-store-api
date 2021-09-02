<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50)->comment('receiver_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('sender_id', 50)->comment('created_by')->nullable();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('notification_id')->nullable();
            $table->string('device_uuid')->nullable();
            $table->string('device_platform')->nullable();
            $table->string('type')->nullable();
            $table->string('user_type')->nullable();
            $table->string('module')->nullable();
            $table->string('title')->nullable();
            $table->string('sub_title')->nullable();
            $table->text('message')->nullable();
            $table->string('image_url')->nullable();
            $table->string('data_id')->nullable();
            $table->string('screen')->nullable();
            $table->boolean('read_status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
