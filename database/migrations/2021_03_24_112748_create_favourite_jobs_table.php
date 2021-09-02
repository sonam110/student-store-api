<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFavouriteJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favourite_jobs', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('job_id', 50)->nullable()->comment('if student then fill job_id and sa_id else sp_id and sa_id');
            $table->foreign('job_id')->references('id')->on('sp_jobs')->onDelete('cascade');
            $table->string('sp_id', 50)->nullable()->comment('Service Provider');
            $table->foreign('sp_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('sa_id', 50)->nullable()->comment('Student');
            $table->foreign('sa_id')->references('id')->on('users')->onDelete('cascade');
            
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
        Schema::dropIfExists('favourite_jobs');
    }
}
