<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoolCompanyFreelancersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cool_company_freelancers', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('cool_company_id');
            $table->enum('paymentAccountTypeId',['Local','International','PayPal'])->default('Local');

            $table->string('bankName');
            $table->string('bankAccountNo');
            $table->string('bankIdentifierCode')->nullable()->comment('if paymentAccountTypeId is Local or International');
            $table->longText('response')->nullable()->comment('complete response saved');

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
        Schema::dropIfExists('cool_company_freelancers');
    }
}
