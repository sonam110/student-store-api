<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorFundTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_fund_transfers', function (Blueprint $table) {
            $table->id();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('transfer_group');
            $table->string('transection_id');
            $table->string('object');
            $table->string('amount');
            $table->string('amount_reversed');
            $table->string('balance_transaction');
            $table->string('created');
            $table->string('currency');
            $table->string('description')->nullable();
            $table->string('destination');
            $table->string('destination_payment');
            $table->string('livemode');
            $table->boolean('reversed')->default(false);
            $table->string('source_type')->nullable();
            $table->longText('complete_response');

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
        Schema::dropIfExists('vendor_fund_transfers');
    }
}
