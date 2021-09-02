<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTagRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tag_relations', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('tag_master_id', 50)->nullable();
            $table->foreign('tag_master_id')->references('id')->on('tag_masters')->onDelete('cascade');
            $table->string('products_services_book_id', 50)->nullable();
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            $table->string('job_id', 50)->nullable();
            $table->foreign('job_id')->references('id')->on('sp_jobs')->onDelete('cascade');
            $table->string('contest_id', 50)->nullable();
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');

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
        Schema::dropIfExists('tag_relations');
    }
}
