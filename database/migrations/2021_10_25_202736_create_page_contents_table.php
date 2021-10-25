<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePageContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('language_id')->constrained()->onDelete('cascade');

            $table->string('page_id');
            $table->foreign('page_id')->references('id')->on('pages')->onDelete('cascade');

            $table->string('title')->nullable();
            $table->string('section_name')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->string('icon_name')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();

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
        Schema::dropIfExists('page_contents');
    }
}
