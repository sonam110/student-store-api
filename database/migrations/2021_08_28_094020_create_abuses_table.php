<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abuses', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('products_services_book_id', 50)->nullable();
            $table->string('contest_id', 50)->nullable();
            $table->string('job_id', 50)->nullable();
            
            $table->string('reason_id_for_abuse')->nullable();
            $table->text('reason_for_abuse')->nullable();
            $table->string('status')->default('pending')->nullable()->comment('pending','approved','rejected');
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
        Schema::dropIfExists('abuses');
    }
}
