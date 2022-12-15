<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_order_deliveries', function (Blueprint $table) {
            $table->increments('id');

            // order
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');

            // delivery
            $table->integer('delivery_profile_id')->unsigned()->nullable();
            $table->foreign('delivery_profile_id')->references('id')->on('ecommerce_delivery_profiles')->onDelete('cascade');
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
        Schema::table('ecommerce_order_deliveries', function (Blueprint $table) {
            $table->dropForeign(['delivery_profile_id']);
            $table->dropForeign(['order_id']);
        });

        Schema::dropIfExists('ecommerce_order_deliveries');
    }
}
