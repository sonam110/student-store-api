<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('job_id', 50);
            $table->foreign('job_id')->references('id')->on('sp_jobs')->onDelete('cascade');
            $table->string('user_cv_detail_id', 50);
            $table->foreign('user_cv_detail_id')->references('id')->on('user_cv_details')->onDelete('cascade');

            $table->string('job_title',100)->nullable();
            $table->string('application_status',100)->nullable();
            $table->dateTime('job_start_date')->nullable();
            $table->dateTime('job_end_date')->nullable();
            $table->text('application_remark')->nullable();
            $table->string('attachment_url')->nullable();
            $table->boolean('is_viewed')->default(0)->nullable();
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
        Schema::dropIfExists('job_applications');
    }
}
