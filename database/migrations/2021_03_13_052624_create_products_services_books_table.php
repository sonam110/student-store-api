<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsServicesBooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products_services_books', function (Blueprint $table) {
            $table->bigInteger('auto_id');
            $table->uuid('id')->primary();

            $table->string('user_id', 50);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('address_detail_id', 50)->nullable();
            $table->foreign('address_detail_id')->references('id')->on('address_details')->onDelete('cascade');
            $table->string('category_master_id', 50)->nullable();
            $table->foreign('category_master_id')->references('id')->on('category_masters')->onDelete('cascade');

            $table->string('sub_category_slug')->nullable()->comment('from category_masters table');
            $table->string('type', 20)->nullable()->comment('product, book, service');
            $table->string('brand')->nullable();

            $table->string('sku', 50)->nullable();
            $table->string('gtin_isbn')->nullable();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();

            $table->float('price')->nullable()->comment('this is actual price before applied discount');
            $table->float('discounted_price')->nullable()->comment('this is final price after applied discount');
            $table->boolean('is_on_offer')->default(0);
            $table->integer('discount_type')->nullable()->comment('0:fixed amount, 1:percentage');
            $table->float('discount_value')->nullable()->comment('amount or percentage value');
            $table->float('shipping_charge')->nullable()->default(0)->comment('for product and book');

            $table->bigInteger('quantity')->nullable()->default(0);

            $table->text('short_summary')->nullable();
            $table->text('description')->nullable()->comment('for all');
            $table->longText('attribute_details')->nullable();
            $table->text('meta_description')->nullable();

            $table->string('sell_type',50)->nullable()->comment('for books: free, for_sale, for_rent');
            $table->float('deposit_amount')->nullable()->comment('if sell_type is for_rent');
            $table->boolean('is_used_item')->nullable()->default(0)->comment('for Books - 0:No, 1:Yes');
            $table->string('item_condition', 20)->nullable()->comment('current condition of the item in percentage.');
            $table->string('author')->nullable()->comment('Author information');
            $table->integer('published_year')->nullable()->comment('XXXX year');
            $table->string('publisher')->nullable()->comment('name of the publisher');
            $table->string('language')->nullable();
            $table->integer('no_of_pages')->nullable();
            $table->string('suitable_age')->nullable()->comment('5-9: 5 to 9 years, 9-13: 9 to 13 years, 13-18: 13 to 18 years, 18+: 18 years and above');
            $table->string('book_cover')->nullable();
            $table->string('dimension_length', 10)->nullable();
            $table->string('dimension_width', 10)->nullable();
            $table->string('dimension_height', 10)->nullable();
            $table->string('weight', 10)->nullable();

            $table->string('service_type',50)->nullable()->comment('online, offline');
            $table->string('delivery_type',50)->nullable()->comment('deliver_to_location, pickup_from_location');
            $table->integer('service_period_time')->nullable()->comment('number');
            $table->string('service_period_time_type', 50)->nullable()->comment('type: hours, days, weeks, months');
            $table->string('service_online_link')->nullable();
            $table->text('service_languages')->nullable();
            $table->text('tags')->nullable();
            
            $table->boolean('is_promoted')->nullable()->default('0');
            $table->dateTime('promotion_start_at')->nullable();
            $table->dateTime('promotion_end_at')->nullable();

            $table->boolean('most_popular')->nullable()->default('0');
            $table->dateTime('most_popular_start_at')->nullable();
            $table->dateTime('most_popular_end_at')->nullable();

            $table->boolean('top_selling')->nullable()->default('0');
            $table->dateTime('top_selling_start_at')->nullable();
            $table->dateTime('top_selling_end_at')->nullable();

            $table->boolean('is_published')->nullable()->default('0');
            $table->dateTime('published_at')->nullable();
            $table->float('avg_rating')->nullable();
            $table->bigInteger('view_count')->nullable();

            $table->boolean('is_reward_point_applicable')->nullable()->default(false);
            $table->string('reward_points')->default('0')->nullable();

            $table->boolean('is_deleted')->nullable()->default('0');
            $table->integer('status')->default('0')->comment('0:Pending, 1:Process, 2: Verified, 3:Rejected, 4:Re-applied');

            $table->boolean('is_sold')->nullable()->default('0');
            $table->boolean('sold_at_student_store')->nullable()->default('0');
            $table->string('days_taken')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_keywords')->nullable();

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
        Schema::dropIfExists('products_services_books');
    }
}
