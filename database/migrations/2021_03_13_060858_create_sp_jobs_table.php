<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sp_jobs', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('address_detail_id', 50)->nullable();
            $table->foreign('address_detail_id')->references('id')->on('address_details')->onDelete('cascade');

            $table->string('category_master_id', 50)->nullable();
            $table->foreign('category_master_id')->references('id')->on('category_masters')->onDelete('cascade');

            $table->string('sub_category_slug',191)->nullable()->default(null)->comment('from category_masters table');
            
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('short_summary')->nullable(); 
            $table->string('job_type',50)->nullable()->comment('part_time:part time, full_time:full time, one_time:one time, contract:contract, internship:internship');
            $table->string('job_nature',30)->nullable()->comment('per_day:per day, per_week:per week, per_month:per month');
            $table->bigInteger('job_hours')->nullable()->comment('if job_type is: part_time, one_time, contract');
            $table->string('job_environment',30)->nullable()->comment('at_office: at office, online: online');
            $table->string('years_of_experience',10)->nullable();
            $table->string('known_languages')->nullable()->comment('comma sepreated');
            $table->longText('description')->nullable();
            $table->longText('duties_and_responsibilities')->nullable();
            $table->longText('nice_to_have_skills')->nullable();
            $table->date('job_start_date')->nullable();
            $table->date('application_start_date')->nullable();
            $table->date('application_end_date')->nullable();
            $table->integer('job_status')->default(1)->comment('0 for inactive,1 for active,2 for rejected,3 for expired,4 for canceled');;
            $table->boolean('is_deleted')->default(0);
            $table->boolean('is_published')->default(0);
            $table->date('published_at')->nullable();
            $table->boolean('is_promoted')->default(0);
            $table->date('promotion_start_date')->nullable();
            $table->date('promotion_end_date')->nullable();
            $table->bigInteger('view_count')->default(0);

            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_jobs');
    }
}
