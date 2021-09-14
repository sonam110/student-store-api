<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSharedRewardPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shared_reward_points', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('sender_id', 50);
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('receiver_id', 50);
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');

            $table->bigInteger('reward_points');
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
        Schema::dropIfExists('shared_reward_points');
    }
}
