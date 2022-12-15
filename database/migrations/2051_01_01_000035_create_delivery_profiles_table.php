<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveryProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_delivery_profiles', function (Blueprint $table) {
            $table->increments('id');
            $table->json('meta')->nullable();
            $table->boolean('is_verified')->default(config('vtlabs_ecommerce.delivery_profile.verify_default'));
            $table->boolean('is_online')->default(false);
            $table->boolean('assigned')->default(false);
            $table->decimal('longitude', 15, 7)->nullable();
            $table->decimal('latitude', 15, 7)->nullable();
            $table->timestamps();

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ecommerce_delivery_profiles');
    }
}