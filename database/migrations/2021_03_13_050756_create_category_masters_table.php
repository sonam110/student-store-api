<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_masters', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('module_type_id', 50);
            $table->foreign('module_type_id')->references('id')->on('module_types')->onDelete('cascade');
            $table->string('category_master_id', 50)->nullable();
            //$table->foreign('category_master_id')->references('id')->on('category_masters')->onDelete('cascade');
            $table->double('vat', 12,2)->default(0);

            $table->string('title');
            $table->string('slug');
            $table->boolean('status')->default(false)->nullable();
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
        Schema::dropIfExists('category_masters');
    }
}
