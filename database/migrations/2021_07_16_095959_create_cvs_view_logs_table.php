<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCvsViewLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cvs_view_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('user_cv_detail_id', 50);
            $table->foreign('user_cv_detail_id')->references('id')->on('user_cv_details')->onDelete('cascade');

            $table->string('applicant_id', 50);
            $table->foreign('applicant_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('user_package_subscription_id', 50)->nullable();
            $table->foreign('user_package_subscription_id')->references('id')->on('user_package_subscriptions')->onDelete('cascade');
            $table->date('valid_till')->nullable();
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
        Schema::dropIfExists('cvs_view_logs');
    }
}
