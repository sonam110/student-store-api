<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceProviderTypeDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_provider_type_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('service_provider_type_id', 50);
            $table->foreign('service_provider_type_id')->references('id')->on('service_provider_types')->onDelete('cascade');

            $table->string('title',100);
            $table->string('slug',100);
            $table->text('description')->nullable();
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
        Schema::dropIfExists('service_provider_type_details');
    }
}
