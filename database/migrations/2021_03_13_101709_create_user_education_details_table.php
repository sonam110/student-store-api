<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserEducationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_education_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('user_cv_detail_id', 50);
            $table->foreign('user_cv_detail_id')->references('id')->on('user_cv_details')->onDelete('cascade');

            $table->string('title')->nullable()->comment('organization name or anything else');
            $table->text('description')->nullable();
            $table->boolean('ongoing')->default(false)->nullable();
            $table->dateTime('from_date')->nullable();
            $table->dateTime('to_date')->nullable();
            $table->boolean('is_from_sweden')->nullable()->comment('if false, country state city will be fillable');
            $table->string('country',150)->nullable();
            $table->string('state',150)->nullable();
            $table->string('city',150)->nullable();
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
        Schema::dropIfExists('user_education_details');
    }
}
