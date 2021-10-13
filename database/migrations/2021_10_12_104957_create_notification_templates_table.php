<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_templates', function (Blueprint $table) {
             $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('template_for')->nullable();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
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
        Schema::dropIfExists('notification_templates');
    }
}
