<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            $table->string('order_id', 50);
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->string('payment_card_detail_id', 50)->nullable();
            $table->foreign('payment_card_detail_id')->references('id')->on('payment_card_details')->onDelete('cascade');

            $table->string('order_item_id')->nullable();
            $table->string('transaction_id')->nullable();
            $table->string('refund_id')->nullable();
            $table->string('object')->nullable();
            $table->string('amount')->nullable();
            $table->string('balance_transaction')->nullable();
            $table->string('charge')->nullable();
            $table->string('created')->nullable();
            $table->string('currency')->nullable();
            $table->string('metadata')->nullable();
            $table->string('payment_intent')->nullable();
            $table->string('reason')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('source_transfer_reversal')->nullable();
            $table->string('status')->nullable();
            $table->string('transfer_reversal')->nullable();

            $table->string('gateway_detail',100)->nullable();
            $table->string('transaction_type',100)->nullable();
            $table->string('transaction_mode',100)->nullable();

            $table->string('card_number')->nullable();
            $table->string('card_type')->nullable();
            $table->string('card_cvv')->nullable();
            $table->string('card_expiry')->nullable();
            $table->string('card_holder_name')->nullable();

            $table->string('quantity',100)->nullable();
            $table->string('price',100)->nullable();
            $table->string('rewards_refund',20)->default(0)->nullable();
            $table->string('reason_for_refund',100)->nullable();
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
        Schema::dropIfExists('refunds');
    }
}
