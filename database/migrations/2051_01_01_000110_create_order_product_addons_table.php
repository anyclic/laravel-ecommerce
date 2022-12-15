<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProductAddonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_order_product_addons', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('total', 8,2);

            // addon choice
            $table->integer('product_addon_choice_id')->unsigned();
            $table->foreign('product_addon_choice_id')->references('id')->on('ecommerce_product_addon_choices')->onDelete('cascade');

            //product
            $table->integer('order_product_id')->unsigned();
            $table->foreign('order_product_id')->references('id')->on('ecommerce_order_products')->onDelete('cascade');

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
        Schema::table('ecommerce_order_product_addons', function (Blueprint $table) {
            $table->dropForeign(['product_addon_choice_id']);
            $table->dropForeign(['order_product_id']);
        });

        Schema::dropIfExists('ecommerce_order_product_addons');
    }
}
