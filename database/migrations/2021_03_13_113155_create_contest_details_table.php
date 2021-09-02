<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContestDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contest_details', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('contest_id', 50);
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            
            $table->string('title');
            $table->string('slug');
            $table->text('short_summary')->nullable();
            $table->text('description')->nullable();
            $table->text('condition_description')->nullable();
            $table->boolean('status')->default(false)->nullable();
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
        Schema::dropIfExists('contest_details');
    }
}
