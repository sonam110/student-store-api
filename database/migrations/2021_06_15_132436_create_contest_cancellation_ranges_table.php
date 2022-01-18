<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContestCancellationRangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contest_cancellation_ranges', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            
            $table->string('contest_id', 50);
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');

            $table->string('from',100);
            $table->string('to',100);
            $table->double('deduct_percentage_value', 12, 2);
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
        Schema::dropIfExists('contest_cancellation_ranges');
    }
}
