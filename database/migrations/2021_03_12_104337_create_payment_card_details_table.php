<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentCardDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_card_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('card_number')->nullable();
            $table->string('card_type')->nullable();
            $table->integer('card_cvv')->nullable();
            $table->string('card_expiry')->nullable();
            $table->string('card_holder_name')->nullable();
            $table->boolean('is_default')->default(false)->nullable();
            $table->boolean('status')->default(false)->nullable();
            $table->boolean('is_minor')->default(false)->nullable();
            $table->string('parent_full_name')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('payment_card_details')->nullable();
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
        Schema::dropIfExists('payment_card_details');
    }
}
