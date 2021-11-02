<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactUsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_us', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            // $table->string('user_id', 50);
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('reason_id')->nullable();
            
            // $table->string('item_type')->nullable();
            $table->string('message_for');
            $table->string('name');
            $table->string('phone');
            $table->string('email');
            // $table->string('title');
            $table->text('message');
            $table->text('images')->nullable();
            // $table->boolean('status')->default(false)->nullable();
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
        Schema::dropIfExists('contact_us');
    }
}
