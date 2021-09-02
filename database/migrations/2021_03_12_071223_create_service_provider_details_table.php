<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceProviderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_provider_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('service_provider_type_id',50);
            $table->foreign('service_provider_type_id')->references('id')->on('service_provider_types')->onDelete('cascade');
            $table->string('registration_type_id',50);
            $table->foreign('registration_type_id')->references('id')->on('registration_types')->onDelete('cascade');
            
            $table->string('company_name',200)->nullable();
            $table->string('organization_number',50)->nullable();
            $table->text('about_company')->nullable();
            $table->string('company_website_url',200)->nullable();
            $table->string('company_logo_path',200)->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->string('vat_number',50)->nullable();
            $table->string('vat_registration_file_path',200)->nullable();
            $table->integer('year_of_establishment')->nullable();
            $table->float('avg_rating')->nullable();
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
        Schema::dropIfExists('service_provider_details');
    }
}
