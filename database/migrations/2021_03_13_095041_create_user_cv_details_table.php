<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserCvDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cv_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('address_detail_id', 50);
            $table->foreign('address_detail_id')->references('id')->on('address_details')->onDelete('cascade');
            
            $table->string('title',100)->nullable();
            $table->text('languages_known')->nullable();
            $table->text('key_skills')->nullable();
            $table->string('preferred_job_env',50)->nullable()->comment('at_office: at office, online: online');
            $table->text('other_description')->nullable();
            $table->boolean('is_published')->default(0)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('cv_url')->nullable();
            $table->string('generated_cv_file')->nullable();
            $table->boolean('cv_update_status')->default(0)->nullable();
            $table->string('total_experience')->default(0)->nullable();
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
        Schema::dropIfExists('user_cv_details');
    }
}
