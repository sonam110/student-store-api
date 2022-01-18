<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('products_services_book_id', 50);
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');

            $table->string('sku',100)->nullable();
            $table->double('price', 12, 2)->nullable();
            $table->string('discount')->nullable();
            $table->bigInteger('quantity')->nullable();
            $table->double('sub_total', 12, 2)->nullable();
            $table->string('item_status',100)->nullable();
            $table->text('note_to_seller')->nullable();
            $table->longText('attribute_data')->nullable();
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
        Schema::dropIfExists('cart_details');
    }
}
