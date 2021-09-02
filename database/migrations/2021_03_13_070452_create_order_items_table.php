<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            
            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('order_id', 50);
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('package_id', 50)->nullable();
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
            $table->string('products_services_book_id', 50)->nullable();
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            $table->string('contest_application_id', 50)->nullable();
            $table->foreign('contest_application_id')->references('id')->on('contest_applications')->onDelete('cascade');
            $table->string('product_type',100)->nullable();
            $table->string('contest_type',50)->nullable();
            $table->string('title',100)->nullable();
            $table->string('sku',100)->nullable();
            $table->float('price')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->float('discount')->nullable();
            $table->string('sell_type',100)->nullable();
            $table->bigInteger('rent_duration')->nullable();
            $table->string('item_status',50)->nullable()->comment('processing, shipped, delivered, completed, cancelled, returned',);
            $table->text('reason_id_for_cancellation')->nullable();
            $table->text('reason_for_cancellation')->nullable();
            $table->string('item_payment_status',100)->nullable()->comment('approved, pending, failed');
            $table->dateTime('expected_delivery_date')->nullable();
            $table->string('tracking_number',100)->nullable();
            $table->dateTime('delivery_completed_date')->nullable();
            $table->dateTime('return_applicable_date')->nullable();
            $table->float('amount_returned')->nullable();
            $table->boolean('is_returned')->nullable()->default(0);
            $table->boolean('is_replaced')->nullable()->default(0);
            $table->boolean('is_disputed')->nullable()->default(0);
            $table->string('cover_image')->nullable();
            $table->text('note_to_seller')->nullable();
            $table->boolean('is_rated')->nullable()->default(0);
            $table->string('shipment_company_name')->nullable();
            $table->bigInteger('order_type')->default('0')->nullable()->comment('0 for fresh order','1 for replacement');
            $table->string('order_item_id')->nullable()->comment('replacement order item id');

            $table->string('earned_reward_points')->nullable()->default(false);
            $table->string('reward_point_status')->default('pending')->nullable()->comment('pending','canceled','credited');

            $table->enum('ask_for_cancellation',['0','1','2','3'])->default('0')->nullable()->comment('1 for request sent, 2 for accepted by buyer, 3 for declined by buyer');
            $table->string('reason_id_for_cancellation_request')->nullable();
            $table->text('reason_for_cancellation_request')->nullable();
            $table->text('reason_id_for_cancellation_request_decline')->nullable();
            $table->text('reason_for_cancellation_request_decline')->nullable();
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
        Schema::dropIfExists('order_items');
    }
}
