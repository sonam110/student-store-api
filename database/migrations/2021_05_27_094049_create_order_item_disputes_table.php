<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemDisputesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_disputes', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('dispute_raised_by', 50);
            $table->foreign('dispute_raised_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('dispute_raised_against', 50);
            $table->foreign('dispute_raised_against')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_item_id', 50);
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->string('products_services_book_id', 50);
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            $table->bigInteger('quantity')->nullable();
            $table->double('amount_to_be_returned', 12, 2)->nullable();
            $table->text('reason_id_for_dispute')->nullable();
            $table->string('dispute')->nullable();
            $table->string('reply')->nullable();
            $table->dateTime('date_of_dispute_completed')->nullable();
            $table->string('reason_id_for_review')->nullable();
            $table->text('review_by_seller')->nullable();
            // $table->string('review_status')->nullable();
            $table->string('dispute_status')->nullable();
            $table->text('reason_id_for_dispute_decline')->nullable();
            $table->text('reason_for_dispute_decline')->nullable();
            $table->text('reason_id_for_review_decline')->nullable();
            $table->text('reason_for_review_decline')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->text('dispute_images')->nullable();
            $table->text('review_images')->nullable();
            $table->text('review_decline_images')->nullable();
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
        Schema::dropIfExists('order_item_disputes');
    }
}
