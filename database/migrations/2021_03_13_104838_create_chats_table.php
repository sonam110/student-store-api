<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('sender', 50);
            $table->foreign('sender')->references('id')->on('users')->onDelete('cascade');
            $table->string('receiver', 50);
            $table->foreign('receiver')->references('id')->on('users')->onDelete('cascade');

            $table->text('message')->nullable();
            $table->string('chat_for',100)->nullable();
            $table->string('item_id')->nullable();
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
        Schema::dropIfExists('chats');
    }
}
