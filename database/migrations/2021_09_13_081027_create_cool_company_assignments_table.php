<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoolCompanyAssignmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cool_company_assignments', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('cool_company_freelancer_id', 50);
            $table->foreign('cool_company_freelancer_id')->references('id')->on('cool_company_freelancers')->onDelete('cascade');

            $table->string('assignment_name');
            $table->longText('send_object')->nullable()->comment('sended object');

            $table->string('assignmentId')->nullable();
            $table->string('agreementId')->nullable();
            $table->string('totalBudget')->nullable();
            $table->string('bdaId')->nullable();
            $table->string('status')->nullable();
            $table->longText('response')->nullable()->comment('complete response saved');

            $table->boolean('is_start_assignment')->default(0)->nullable();
            $table->date('start_assignment_date')->nullable();
            $table->longText('start_assignment_response')->nullable()->comment('if state change Start');

            $table->boolean('is_complete_assignment')->default(0)->nullable();
            $table->date('complete_assignment_date')->nullable();
            $table->longText('complete_assignment_response')->nullable()->comment('if state change to Completed');

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
        Schema::dropIfExists('cool_company_assignments');
    }
}
