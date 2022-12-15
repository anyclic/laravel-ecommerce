<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendorsTable extends Migration
{
    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        Schema::create('ecommerce_vendors', function (Blueprint $table) {
            $table->increments('id');
            $table->json('name')->nullable();
            $table->json('tagline')->nullable();
            $table->json('details')->nullable();
            $table->json('meta')->nullable();
            $table->integer('minimum_order')->unsigned()->default(0);
            $table->integer('delivery_fee')->unsigned()->default(0);
            $table->string('area')->nullable();
            $table->string('address')->nullable();
            $table->decimal('longitude', 15, 7)->default(0.0);
            $table->decimal('latitude', 15, 7)->default(0.0);
            $table->boolean('is_verified')->default(config('vtlabs_ecommerce.vendor.verify_default'));
            
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->timestamps();
        });

        Schema::create('ecommerce_vendor_categories', function (Blueprint $table) {
            $table->integer('vendor_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->foreign('vendor_id')->references('id')->on('ecommerce_vendors')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on(config('vtlabs_category.tables.category'))->onDelete('cascade');
        });
    }
    
    /**
    * Reverse the migrations.
    *
    * @return void
    */
    public function down()
    {
        Schema::dropIfExists('ecommerce_vendor_categories');
        Schema::dropIfExists('ecommerce_vendors');
    }
}
