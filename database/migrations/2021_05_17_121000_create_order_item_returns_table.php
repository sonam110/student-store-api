<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_item_returns', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('return_address_id', 50);
            $table->foreign('return_address_id')->references('id')->on('address_details')->onDelete('cascade');
            $table->string('order_item_id', 50);
            $table->foreign('order_item_id')->references('id')->on('order_items')->onDelete('cascade');
            $table->string('products_services_book_id', 50);
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            
            $table->bigInteger('quantity')->nullable();
            $table->string('return_type')->nullable()->comment('by_hand','by_shipment');
            $table->string('shipment_company_name')->nullable();
            $table->dateTime('date_of_return_initiated');
            $table->string('reason__id_for_return')->nullable();
            $table->text('reason_of_return')->nullable();
            $table->text('images')->nullable();
            $table->float('amount_to_be_returned')->nullable();
            $table->bigInteger('return_card_number')->nullable();
            $table->string('return_card_holder_name')->nullable();
            $table->string('return_tracking_number')->nullable();
            $table->dateTime('expected_return_date')->nullable();
            $table->dateTime('date_of_return_completed')->nullable();
            $table->string('first_name',100);
            $table->string('last_name',100)->nullable();
            $table->string('email',100)->nullable();
            $table->bigInteger('contact_number')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('country',100)->nullable();
            $table->string('state',100)->nullable();
            $table->string('city',100)->nullable();
            $table->text('full_address')->nullable();
            $table->string('return_status')->nullable();
            $table->string('reason__id_for_return_decline')->nullable();
            $table->text('reason_for_return_decline')->nullable();
            $table->string('return_code')->nullable();
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
        Schema::dropIfExists('order_item_returns');
    }
}
