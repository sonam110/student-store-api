<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPackageSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_package_subscriptions', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();
            
            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('package_id', 50);
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');

            $table->string('subscription_id', 150)->nullable();
            $table->string('payby', 50)->nullable()->comment('stripe,klarna,swish');
            $table->boolean('is_canceled')->nullable()->default(0);
            $table->string('canceled_date')->nullable();
            $table->longText('response_request')->nullable();

            
            $table->dateTime('package_valid_till')->nullable();
            $table->string('subscription_status',50)->nullable();
            $table->string('remark',200)->nullable();
            $table->string('module')->nullable();
            $table->string('type_of_package')->nullable();
            $table->integer('job_ads')->nullable()->default(0);
            $table->integer('publications_day')->nullable()->default(0);
            $table->integer('duration')->nullable()->default(0);
            $table->integer('cvs_view')->nullable()->default(0);
            $table->integer('employees_per_job_ad')->nullable()->default(0);
            $table->integer('no_of_boost')->nullable()->default(0);
            $table->integer('boost_no_of_days')->nullable()->default(0);
            $table->integer('most_popular')->nullable()->default(0);
            $table->integer('most_popular_no_of_days')->nullable()->default(0);
            $table->integer('top_selling')->nullable()->default(0);
            $table->integer('top_selling_no_of_days')->nullable()->default(0);
            $table->decimal('price', 9, 2)->nullable()->default(0)->comment('Sponsar cost as well');
            $table->decimal('start_up_fee', 9, 2)->nullable()->default(0);
            $table->decimal('subscription', 9, 2)->nullable()->default(0);
            $table->decimal('commission_per_sale', 9, 2)->nullable()->comment('in %')->default(0);
            $table->integer('number_of_product')->nullable()->default(0)->comment('-1 means unlimited');
            $table->integer('number_of_service')->nullable()->default(0)->comment('-1 means unlimited');
            $table->integer('number_of_book')->nullable()->default(0)->comment('-1 means unlimited');
            $table->integer('number_of_contest')->nullable()->default(0);
            $table->integer('number_of_event')->nullable()->default(0);
            $table->integer('notice_month')->nullable()->default(0)->comment('This is the time for to close the subscription');
            $table->integer('locations')->nullable();
            $table->integer('organization')->nullable();
            $table->integer('attendees')->nullable();
            $table->boolean('range_of_age')->nullable()->default(0);
            $table->boolean('cost_for_each_attendee')->nullable()->default(0);
            $table->decimal('top_up_fee', 9, 2)->nullable();
            $table->integer('used_no_of_boost')->nullable()->default(0);
            $table->integer('used_most_popular')->nullable()->default(0);
            $table->integer('used_top_selling')->nullable()->default(0);
            $table->integer('used_job_ads')->nullable()->default(0);
            $table->integer('used_cvs_view')->nullable()->default(0);
            $table->integer('used_number_of_product')->nullable()->default(0);
            $table->integer('used_number_of_service')->nullable()->default(0);
            $table->integer('used_number_of_book')->nullable()->default(0);
            $table->integer('used_number_of_contest')->nullable()->default(0);
            $table->integer('used_number_of_event')->nullable()->default(0);
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
        Schema::dropIfExists('user_package_subscriptions');
    }
}
