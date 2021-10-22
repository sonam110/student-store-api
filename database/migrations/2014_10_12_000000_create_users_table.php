<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->foreignId('language_id',10)->constrained()->onDelete('cascade');
            $table->foreignId('user_type_id',100)->constrained()->onDelete('cascade');
            
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('dob')->nullable();
            $table->string('email')->unique();
            $table->string('contact_number')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_email_verified')->nullable();
            $table->boolean('is_contact_number_verified')->nullable();
            $table->string('profile_pic_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->string('profile_pic_thumb_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->text('short_intro')->nullable();
            $table->string('qr_code_img_path')->nullable();
            $table->bigInteger('qr_code_number')->nullable();
            $table->dateTime('qr_code_valid_till')->nullable();
            $table->integer('reward_points')->nullable()->default('0');
            $table->string('cp_first_name')->nullable();
            $table->string('cp_last_name')->nullable();
            $table->string('cp_email')->nullable();
            $table->string('cp_contact_number')->nullable();
            $table->string('cp_gender')->nullable();
            $table->boolean('is_minor')->nullable();
            $table->string('guardian_first_name')->nullable();
            $table->string('guardian_last_name')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_password')->nullable();
            $table->string('guardian_contact_number')->nullable();
            $table->boolean('is_guardian_email_verified')->nullable();
            $table->boolean('is_guardian_contact_number_verified')->nullable();
            $table->boolean('is_verified')->default(false)->nullable();
            $table->boolean('is_agreed_on_terms')->default(false)->nullable();
            $table->boolean('is_prime_user')->default(false)->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->boolean('show_email')->default(false)->nullable();
            $table->boolean('show_contact_number')->default(false)->nullable();
            $table->string('social_security_number')->nullable();
            $table->bigInteger('status')->default('0')->nullable()->comment('0 for not active , 1 for active, 2 for blocked');
            $table->dateTime('last_login');

            $table->enum('bank_account_type',['1','2','3'])->default('1')->nullable()->comment('1:Local, 2:International, 3:Paypal');
            $table->string('bank_name')->nullable();
            $table->string('bank_account_num')->nullable();
            $table->string('bank_identifier_code')->nullable();

            $table->string('stripe_account_id')->nullable();
            $table->enum('stripe_status',['1','2','3','4'])->default('1')->nullable()->comment('1:Pending, 2:Process, 3: Activate, 4: Failed');
            $table->timestamps('stripe_create_timestamp')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('klarna_customer_token')->nullable();
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
        Schema::dropIfExists('users');
    }
}
