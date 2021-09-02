<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_lists', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            $table->string('job_id', 50)->nullable();
            $table->foreign('job_id')->references('id')->on('sp_jobs')->onDelete('cascade');
            $table->string('products_services_book_id', 50)->nullable();
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            $table->string('contest_id', 50)->nullable();
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            $table->string('buyer_id', 50);
            $table->foreign('buyer_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('seller_id', 50);
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('contact_lists');
    }
}
