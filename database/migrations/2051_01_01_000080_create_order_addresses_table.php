<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_order_addresses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            
            $table->string('formatted_address');
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->decimal('longitude', 15, 7);
            $table->decimal('latitude', 15, 7);

            // order
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('ecommerce_orders')->onDelete('cascade');

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
        Schema::table('ecommerce_order_addresses', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
        });
        
        Schema::dropIfExists('ecommerce_order_addresses');
    }
}
