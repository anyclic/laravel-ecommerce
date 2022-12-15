<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorProductCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_vendor_product_categories', function (Blueprint $table) {
            $table->integer('vendor_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->integer('product_id')->unsigned();
            $table->foreign('vendor_id')->references('id')->on('ecommerce_vendors')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on(config('vtlabs_category.tables.category'))->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_vendor_product_categories', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['category_id']);
            $table->dropForeign(['product_id']);
        });

        // delete products
        Schema::dropIfExists('ecommerce_vendor_products');
    }
}
