<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryOrderRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_delivery_order_requests', function (Blueprint $table) {
            $table->increments('id');

            $table->json('meta')->nullable();

            // delivery profile
            $table->integer('delivery_profile_id')->unsigned();
            $table->foreign('delivery_profile_id')->references('id')->on('ecommerce_delivery_profiles')->onDelete('cascade');

            // order
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');
            
            $table->timestamps();

            $table->unique(['delivery_profile_id', 'order_id'], 'unique_delivery_profile_order');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_delivery_order_requests', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['delivery_profile_id']);
        });

        Schema::dropIfExists('ecommerce_delivery_order_requests');
    }
}
