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
            
            $table->string('first_name',100);
            $table->string('last_name',100)->nullable();
            $table->string('gender',10)->nullable();
            $table->date('dob')->nullable();
            $table->string('email',100)->unique();
            $table->bigInteger('contact_number')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_email_verified')->nullable();
            $table->boolean('is_contact_number_verified')->nullable();
            $table->string('profile_pic_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->text('short_intro')->nullable();
            $table->string('qr_code_img_path')->nullable();
            $table->bigInteger('qr_code_number')->nullable();
            $table->dateTime('qr_code_valid_till')->nullable();
            $table->integer('reward_points')->nullable()->default('0');
            $table->string('cp_first_name')->nullable();
            $table->string('cp_last_name')->nullable();
            $table->string('cp_email')->nullable();
            $table->bigInteger('cp_contact_number')->nullable();
            $table->string('cp_gender',10)->nullable();
            $table->boolean('is_minor')->nullable();
            $table->string('guardian_first_name')->nullable();
            $table->string('guardian_last_name')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_password')->nullable();
            $table->bigInteger('guardian_contact_number')->nullable();
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
