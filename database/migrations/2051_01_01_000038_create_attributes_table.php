<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_attributes', function (Blueprint $table) {
            $table->increments('id');
            $table->json('title');
            $table->timestamps();
        });

        Schema::create('ecommerce_attribute_terms', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');

            $table->integer('attribute_id')->unsigned();
            $table->foreign('attribute_id')->references('id')->on('ecommerce_attributes')->onDelete('cascade');
            
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
        Schema::dropIfExists('ecommerce_attribute_terms');
        Schema::dropIfExists('ecommerce_attributes');
    }
}
