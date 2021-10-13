<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaySettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            $table->string('payment_gateway_name')->nullable();
            $table->string('payment_gateway_key')->nullable();
            $table->string('payment_gateway_secret')->nullable();
            $table->string('stripe_currency')->default('SEK')->nullable();
            $table->string('klarna_username')->nullable();
            $table->string('klarna_password')->nullable();
            $table->string('swish_access_token')->nullable();
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
        Schema::dropIfExists('payment_gateway_settings');
    }
}
