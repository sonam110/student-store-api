<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_tags', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('cv_id', 50)->nullable();
            $table->foreign('cv_id')->references('id')->on('user_cv_details')->onDelete('cascade');
            $table->string('job_id', 50)->nullable();
            $table->foreign('job_id')->references('id')->on('sp_jobs')->onDelete('cascade');
            $table->string('user_id', 50)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('title')->nullable();
            $table->string('nice_to_have')->nullable();
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
        Schema::dropIfExists('job_tags');
    }
}
