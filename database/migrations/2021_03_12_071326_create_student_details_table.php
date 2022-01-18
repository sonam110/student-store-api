<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStudentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('enrollment_no',100)->nullable();
            $table->string('education_level',100)->nullable();
            $table->string('board_university',100)->nullable();
            $table->string('institute_name',100)->nullable();
            $table->smallInteger('no_of_years_of_study')->nullable();
            $table->string('student_id_card_img_path',191)->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->bigInteger('completion_year')->nullable();
            $table->double('avg_rating', 12, 2)->nullable();
            $table->boolean('status')->default(false)->nullable();
            $table->string('cool_company_id', 50)->nullable();
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
        Schema::dropIfExists('student_details');
    }
}
