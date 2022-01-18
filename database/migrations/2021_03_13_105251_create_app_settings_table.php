<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('reward_point_setting_id', 50)->nullable();
            $table->foreign('reward_point_setting_id')->references('id')->on('reward_point_settings')->onDelete('cascade');

            $table->string('app_name')->nullable();
            $table->string('org_number')->nullable();
            $table->text('description')->nullable();
            $table->string('logo_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->string('logo_thumb_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->text('allowed_app_version')->nullable();
            $table->string('invite_url')->nullable();
            $table->string('play_store_url')->nullable();
            $table->string('app_store_url')->nullable();
            $table->string('copyright_text')->nullable();
            $table->string('fb_ur')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('insta_url')->nullable();
            $table->string('linked_url')->nullable();
            $table->string('support_email')->nullable();
            $table->string('support_contact_number')->nullable();
            $table->text('address')->nullable();
            $table->string('single_rewards_pt_value')->nullable();
            $table->string('customer_rewards_pt_value')->nullable();
            $table->longText('reward_points_policy')->nullable();
            $table->double('vat',[10,2])->nullable()->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_enabled_cool_company')->default(1);
            $table->integer('coolCompanyVatRateId')->default('7');
            $table->double('coolCompanyCommission',[10,2])->nullable()->default('5.98');
            $table->double('cool_company_social_fee_percentage', 12, 2)->nullable()->default('31.42');
            $table->double('cool_company_salary_tax_percentage', 12, 2)->nullable()->default(30);
            $table->boolean('is_job_mod_enabled')->default(1);
            $table->boolean('is_product_mod_enabled')->default(1);
            $table->boolean('is_service_mod_enabled')->default(1);
            $table->boolean('is_book_mod_enabled')->default(1);
            $table->boolean('is_contest_mod_enabled')->default(1);
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
        Schema::dropIfExists('app_settings');
    }
}
