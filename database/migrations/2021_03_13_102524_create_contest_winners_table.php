<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContestWinnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contest_winners', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            $table->string('contest_application_id')->nullable();
            $table->foreign('contest_application_id')->references('id')->on('contest_applications')->onDelete('cascade');
            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('contest_id', 50);
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            $table->string('prize')->nullable();
            $table->string('contest_prize_detail')->nullable();

            $table->text('remark')->nullable();
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
        Schema::dropIfExists('contest_winners');
    }
}
