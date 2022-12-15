<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ecommerce_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('notes', 255)->nullable();
            $table->json('meta')->nullable();

            $table->boolean('is_guest')->default(false);
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_mobile')->nullable();

            $table->double('subtotal', 8,2);
            $table->double('taxes', 8,2);
            $table->double('delivery_fee', 8,2);
            $table->double('total', 8,2);
            $table->double('discount', 8,2);

            // scheduling
            $table->enum('type', ['ASAP', 'LATER'])->default('ASAP');
            $table->dateTime('scheduled_on')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            // vendor
            $table->integer('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('ecommerce_vendors')->onDelete('set null');
            
            // customer
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::table('ecommerce_orders', function (Blueprint $table) {
            $table->dropForeign(['vendor_id']);
            $table->dropForeign(['user_id']);
        });

        // delete orders
        Schema::dropIfExists('ecommerce_orders');
    }
}
