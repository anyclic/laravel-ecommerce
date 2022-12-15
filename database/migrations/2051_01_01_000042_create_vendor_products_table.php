<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_vendor_products', function (Blueprint $table) {
            $table->increments('id');

            // price and sales
            $table->double('price', 8,2);
            $table->double('sale_price', 8,2)->nullable();
            $table->date('sale_price_from')->nullable();
            $table->date('sale_price_to')->nullable();

            // stock management
            $table->integer('stock_quantity')->default(-1); // -1 implies unlimited availability
            $table->integer('stock_low_threshold')->default(0);

            $table->integer('vendor_id')->unsigned();
            $table->foreign('vendor_id')->references('id')->on('ecommerce_vendors')->onDelete('cascade');

            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
            
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
        Schema::table('ecommerce_vendor_products', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['product_id']);
        });

        // delete products
        Schema::dropIfExists('ecommerce_vendor_products');
    }
}
