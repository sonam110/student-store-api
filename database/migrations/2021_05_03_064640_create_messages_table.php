<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_from_id', 50);
            $table->foreign('user_from_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('user_to_id', 50);
            $table->foreign('user_to_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('job_id', 50)->nullable();
            $table->foreign('job_id')->references('id')->on('sp_jobs')->onDelete('cascade');
            $table->string('products_services_book_id', 50)->nullable();
            $table->foreign('products_services_book_id')->references('id')->on('products_services_books')->onDelete('cascade');
            $table->string('contest_id', 50)->nullable();
            $table->foreign('contest_id')->references('id')->on('contests')->onDelete('cascade');
            
            $table->string('title')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->enum('status',['read','unread'])->default('unread')->nullable();
            $table->string('message_type')->nullable()->comment('buyer','seller');
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
        Schema::dropIfExists('messages');
    }
}
