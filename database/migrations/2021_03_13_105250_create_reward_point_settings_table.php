<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRewardPointSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reward_point_settings', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            $table->bigInteger('reward_points')->nullable();
            $table->double('equivalent_currency_value', 12 ,2)->nullable();
            $table->dateTime('applicable_from')->nullable();
            $table->dateTime('applicable_to')->nullable();
            $table->string('min_range',200)->nullable();
            $table->bigInteger('min_value')->nullable();
            $table->string('mid_range',200)->nullable();
            $table->bigInteger('mid_value')->nullable();
            $table->string('max_range',200)->nullable();
            $table->bigInteger('max_value')->nullable();
            $table->boolean('status')->default(false)->nullable();
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
        Schema::dropIfExists('reward_point_settings');
    }
}
