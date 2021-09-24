<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('template_for')->nullable();
            // $table->string('to')->nullable();
            // $table->string('cc')->nullable();
            // $table->string('bcc')->nullable();
            $table->string('from')->nullable();
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('attachment_path')->nullable();
            $table->text('attributes')->nullable();
            $table->boolean('status')->default(true)->nullable();
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
        Schema::dropIfExists('email_templates');
    }
}
