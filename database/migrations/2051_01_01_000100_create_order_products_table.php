<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_order_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quantity');
            $table->decimal('total', 8,2);

            // order
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');

            //product
            $table->integer('vendor_product_id')->unsigned();
            $table->foreign('vendor_product_id')->references('id')->on('ecommerce_vendor_products')->onDelete('cascade');

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
        Schema::table('ecommerce_order_products', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['vendor_product_id']);
        });

        Schema::dropIfExists('ecommerce_order_products');
    }
}
