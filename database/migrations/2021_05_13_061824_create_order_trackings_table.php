<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTrackingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_trackings', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('order_item_id', 50);
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');

            $table->string('status')->default(0)->nullable();
            $table->text('comment')->nullable();
            $table->string('type')->nullable()->default('delivery')->comment('delivery','replacement','return','dispute');
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
        Schema::dropIfExists('order_trackings');
    }
}
