<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contests', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50)->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('address_detail_id', 50)->nullable();
            $table->foreign('address_detail_id')->references('id')->on('address_details')->onDelete('cascade');
            $table->string('category_master_id', 50)->nullable();
            $table->foreign('category_master_id')->references('id')->on('category_masters')->onDelete('cascade');

            $table->string('service_provider_type_id',50);
            $table->foreign('service_provider_type_id')->references('id')->on('service_provider_types')->onDelete('cascade');
            $table->string('registration_type_id',50);
            $table->foreign('registration_type_id')->references('id')->on('registration_types')->onDelete('cascade');

            $table->string('sub_category_slug')->nullable()->comment('from category_masters table');

            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->enum('type',['contest','event'])->nullable();
            // $table->string('contest_type',100)->nullable();
            // $table->string('event_type',100)->nullable();
            $table->string('cover_image_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->string('cover_image_thumb_path')->default('https://www.nrtsms.com/images/no-image.png')->nullable();
            $table->text('sponsor_detail')->nullable();
            $table->date('start_date')->nullable();
            // $table->date('end_date')->nullable();
            $table->text('tags')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->date('application_start_date')->nullable();
            $table->date('application_end_date')->nullable();
            $table->bigInteger('max_participants')->nullable();
            $table->bigInteger('no_of_winners')->nullable();
            $table->text('winner_prizes')->nullable();
            $table->string('mode',100)->nullable();
            $table->string('meeting_link')->nullable();
            $table->string('address')->nullable();
            $table->string('target_country')->nullable();
            $table->string('target_city')->nullable();
            $table->text('education_level')->nullable();
            $table->text('educational_institition')->nullable();
            $table->boolean('age_restriction')->nullable();
            $table->bigInteger('min_age')->nullable();
            $table->bigInteger('max_age')->nullable();
            $table->string('others')->nullable();
            $table->boolean('condition_for_joining')->nullable();
            $table->string('available_for', 50)->nullable();
            $table->text('condition_description')->nullable();
            $table->string('condition_file_path')->nullable();
            $table->text('jury_members')->nullable();
            $table->boolean('is_free')->default(false)->nullable();
            $table->float('basic_price_wo_vat')->nullable()->comment('this is basic price without vat');
            $table->float('subscription_fees')->nullable()->default('0');
            $table->boolean('use_cancellation_policy')->nullable();
            $table->boolean('provide_participation_certificate')->nullable();
            $table->boolean('is_on_offer')->default(false)->nullable();
            $table->string('discount_type',100)->nullable();
            $table->float('discount_value')->nullable();
            $table->float('discounted_price')->nullable();
            $table->boolean('is_published')->default(false)->nullable();
            $table->dateTime('published_at')->nullable();
            $table->boolean('is_deleted')->default(false)->nullable();
            $table->boolean('required_file_upload')->default(false)->nullable();
            $table->string('file_title')->nullable();
            $table->boolean('is_reward_point_applicable')->nullable()->default(false);
            $table->string('reward_points')->default('0')->nullable();

            $table->boolean('is_min_participants')->nullable()->default(false);
            $table->string('min_participants')->default('0')->nullable();
            
            $table->string('status')->default('pending')->nullable()->comment('pending,verified,rejected,canceled,completed,hold');
            $table->string('reason_id_for_cancellation')->nullable();
            $table->text('reason_for_cancellation')->nullable();
            $table->string('reason_id_for_rejection')->nullable();
            $table->text('reason_for_rejection')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
            
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
        Schema::dropIfExists('contests');
    }
}
