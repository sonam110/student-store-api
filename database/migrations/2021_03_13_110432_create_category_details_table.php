<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('category_master_id', 50);
            $table->foreign('category_master_id')->references('id')->on('category_masters')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            
            $table->string('title',100);
            $table->string('slug',100);
            $table->text('description')->nullable();
            $table->boolean('is_parent')->nullable()->default(0);
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
        Schema::dropIfExists('category_details');
    }
}
