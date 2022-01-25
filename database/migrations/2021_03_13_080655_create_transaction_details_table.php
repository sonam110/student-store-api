<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('order_id', 50);
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            
            $table->string('payment_card_detail_id', 50)->nullable();
            $table->foreign('payment_card_detail_id')->references('id')->on('payment_card_details')->onDelete('cascade');

            $table->string('user_package_subscription_id', 50)->nullable();
            $table->enum('payment_status', ['processing','paid','failed'])->default('processing')->nullable();
            
            $table->string('transaction_id',100)->nullable();
            $table->string('transaction_reference_no',100)->nullable();
            $table->string('transaction_type',100)->nullable();
            $table->string('transaction_mode',100)->nullable();
            $table->string('transaction_status')->nullable();
            $table->double('transaction_amount', 12 ,2)->nullable();
            $table->string('gateway_detail',100)->nullable();

            $table->string('transaction_timestamp',50)->nullable();
            $table->string('currency',50)->nullable();

            $table->text('description')->nullable();
            $table->string('receipt_email')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('refund_url')->nullable();

            $table->string('card_number')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_cvv')->nullable();
            $table->string('card_expiry')->nullable();
            $table->string('card_holder_name')->nullable();
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
        Schema::dropIfExists('transaction_details');
    }
}
