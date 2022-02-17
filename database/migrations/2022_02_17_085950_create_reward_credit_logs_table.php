<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardCreditLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_credit_logs', function (Blueprint $table) {
            $table->id();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_item_id', 50)->nullable();
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');

            $table->bigInteger('reward');

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
        Schema::dropIfExists('reward_credit_logs');
    }
}
