<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_products', function (Blueprint $table) {
            $table->increments('id');
            $table->json('title')->nullable();
            $table->json('detail')->nullable();
            $table->json('meta')->nullable();

            // price
            $table->double('price', 8,2);

            // TODO: remove, attributes for variations, stores json data
            // $table->text('attributes')->nullable();
            // $table->text('selected_attributes')->nullable();

            $table->enum('owner', ['admin', 'vendor'])->default('vendor');

            // assign parent if variable product
            $table->integer('parent_id')->unsigned()->nullable();

            $table->integer('attribute_term_id')->unsigned()->nullable();
            $table->foreign('attribute_term_id')->references('id')->on('ecommerce_attribute_terms')->onDelete('cascade');
            
            $table->timestamps();
        });

        Schema::create('ecommerce_product_addon_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->json('title');
            $table->unsignedInteger('max_choices'); // maximum number of choices user can pick
            $table->unsignedInteger('min_choices'); // minimum number of choices user should pick
            
            $table->integer('product_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
            
            $table->timestamps();
        });

        Schema::create('ecommerce_product_addon_choices', function (Blueprint $table) {
            $table->increments('id');
            $table->json('title');
            $table->double('price', 8,2);
            
            $table->integer('product_addon_group_id')->unsigned();
            $table->foreign('product_addon_group_id')->references('id')->on('ecommerce_product_addon_groups')->onDelete('cascade');
            
            $table->timestamps();
        });

        Schema::create('ecommerce_product_categories', function (Blueprint $table) {
            $table->integer('product_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->foreign('product_id')->references('id')->on('ecommerce_products')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on(config('vtlabs_category.tables.category'))->onDelete('cascade');

            $table->unique(['product_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // delete product categories
        Schema::dropIfExists('ecommerce_product_categories');

        // delete choices
        Schema::dropIfExists('ecommerce_product_addon_choices');

        // delete groups
        Schema::dropIfExists('ecommerce_product_addon_groups');

        // delete products
        Schema::dropIfExists('ecommerce_products');
    }
}
