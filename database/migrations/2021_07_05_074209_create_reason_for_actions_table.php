<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReasonForActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reason_for_actions', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

             $table->string('module_type_id', 50)->nullable();
            $table->foreign('module_type_id')->references('id')->on('module_types')->onDelete('cascade');
            
            $table->foreignId('language_id')->constrained()->onDelete('cascade');
            $table->string('action')->nullable();
            $table->text('reason_for_action')->nullable();
            $table->boolean('status')->default(true)->nullable();
            $table->boolean('text_field_enabled')->default(false)->nullable();
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
        Schema::dropIfExists('reason_for_actions');
    }
}
