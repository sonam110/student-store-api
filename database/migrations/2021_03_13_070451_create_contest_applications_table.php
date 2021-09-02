<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContestApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contest_applications', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('contest_id', 50);
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');

            $table->string('contest_type',50)->nullable();
            $table->string('contest_title',100)->nullable();
            $table->string('application_status',100)->nullable();
            $table->string('subscription_status',100)->nullable();
            $table->text('application_remark')->nullable();
            $table->text('subscription_remark')->nullable();
            $table->text('document')->nullable();
            $table->string('reason_id_for_cancellation')->nullable();
            $table->text('reason_for_cancellation')->nullable();
            $table->string('reason_id_for_rejection')->nullable();
            $table->text('reason_for_rejection')->nullable();
            $table->string('cancelled_by')->nullable();
            $table->string('winner')->nullable();
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
        Schema::dropIfExists('contest_applications');
    }
}
