<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBucketGroupAttributeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bucket_group_attribute_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('bucket_group_attribute_id', 50);
            $table->foreign('bucket_group_attribute_id')->references('id')->on('bucket_group_attributes')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            
            $table->string('name')->nullable();
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
        Schema::dropIfExists('bucket_group_attribute_details');
    }
}
