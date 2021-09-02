<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDisputeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_dispute_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('dispute_raised_by_user', 50);
            $table->foreign('dispute_raised_by_user')->references('id')->on('users')->onDelete('cascade');
            $table->string('dispute_raised_for_user', 50);
            $table->foreign('dispute_raised_for_user')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_item_id', 50);
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');

            $table->text('comment_by_consumer')->nullable();
            $table->text('comment_by_provider')->nullable();
            $table->text('comment_by_admin')->nullable();
            $table->string('dispute_status',50)->nullable();
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
        Schema::dropIfExists('user_dispute_details');
    }
}
