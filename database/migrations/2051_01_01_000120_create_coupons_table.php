<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCouponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_coupons', function (Blueprint $table) {
            $table->increments('id');
            
            $table->json('title')->nullable();
            $table->json('detail')->nullable();
            $table->json('meta')->nullable();

            $table->string('code', 32)->unique();
            $table->integer('reward');
            $table->enum('type', ['fixed', 'percent']);
            $table->date('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('ecommerce_coupons_users', function (Blueprint $table) {
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('coupon_id');
            $table->timestamp('used_at');

            $table->primary(['user_id', 'coupon_id']);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('ecommerce_coupons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_coupons_users', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['coupon_id']);
        });

        Schema::dropIfExists('ecommerce_coupons_users');

        Schema::dropIfExists('ecommerce_coupons');
    }
}
