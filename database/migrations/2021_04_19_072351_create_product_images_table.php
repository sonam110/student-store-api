<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('products_services_book_id', 50);
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            
            $table->string('image_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->string('thumb_image_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->boolean('cover')->nullable();
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
        Schema::dropIfExists('product_images');
    }
}
