<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingAndFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_and_feedback', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('order_item_id', 50)->nullable();
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->string('products_services_book_id', 50);
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            $table->string('from_user', 50)->nullable();
            $table->foreign('from_user')->references('id')->on('users')->onDelete('cascade');
            $table->string('to_user', 50)->nullable();
            $table->foreign('to_user')->references('id')->on('users')->onDelete('cascade');

            $table->string('user_name', 50)->nullable();

            $table->bigInteger('product_rating')->nullable();
            $table->bigInteger('user_rating')->nullable();
            $table->text('product_feedback')->nullable();
            $table->text('user_feedback')->nullable();
            $table->boolean('is_feedback_approved');
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
        Schema::dropIfExists('rating_and_feedback');
    }
}
