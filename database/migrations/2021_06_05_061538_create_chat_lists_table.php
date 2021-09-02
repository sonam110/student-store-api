<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chat_lists', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            $table->string('contact_list_id', 50);
            $table->foreign('contact_list_id')->references('id')->on('contact_lists')->onDelete('cascade');
            $table->string('sender_id', 50);
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('receiver_id', 50);
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
            $table->longText('message')->nullable();
            $table->enum('status',['read','unread'])->default('unread')->nullable();
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
        Schema::dropIfExists('chat_lists');
    }
}
