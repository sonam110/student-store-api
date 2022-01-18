<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            
            $table->string('order_number', 50)->nullable();
            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('address_detail_id', 50)->nullable();
            $table->foreign('address_detail_id')->references('id')->on('address_details')->onDelete('cascade');

            $table->string('order_status',100)->nullable()->comment('pending, process, ready-to-deliver, delivered, cancelled, completed');
            $table->double('sub_total', 12, 2)->nullable();
            $table->double('item_discount', 12, 2)->nullable();
            $table->double('vat', 12, 2)->nullable();
            $table->double('shipping_charge', 12, 2)->nullable();
            $table->double('total', 12, 2)->nullable();
            $table->string('promo_code',100)->nullable();
            $table->double('promo_code_discount', 12, 2)->nullable();
            $table->double('grand_total', 12, 2)->nullable();
            $table->text('remark')->nullable();
            $table->string('first_name',100);
            $table->string('last_name',100)->nullable();
            $table->string('email',100)->nullable();
            $table->bigInteger('contact_number')->nullable();
            $table->string('latitude',50)->nullable();
            $table->string('longitude',50)->nullable();
            $table->string('country',100)->nullable();
            $table->string('state',100)->nullable();
            $table->string('city',100)->nullable();
            $table->text('full_address')->nullable();
            $table->string('used_reward_points')->nullable()->default('0');
            $table->string('reward_point_status')->nullable()->default('used');
            $table->double('payable_amount', 12, 2)->nullable();
            $table->string('order_for')->nullable();
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
        Schema::dropIfExists('orders');
    }
}
