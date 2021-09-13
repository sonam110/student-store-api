<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttributeMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attribute_masters', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            // $table->string('category_master_id', 50);
            // $table->foreign('category_master_id')->references('id')->on('category_masters')->onDelete('cascade');

            $table->string('category_master_slug', 255);

            $table->string('bucket_group_id', 50);
            $table->foreign('bucket_group_id')->references('id')->on('bucket_groups')->onDelete('cascade');
            
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
        Schema::dropIfExists('attribute_masters');
    }
}
