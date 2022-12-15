<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterVendorsAddStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ecommerce_vendors', function (Blueprint $table) {
            $table->integer('orders_count')->unsigned()->default(0);
            $table->double('average_ratings', 8, 2)->default(0.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ecommerce_vendors', function (Blueprint $table) {
            $table->dropColumn('orders_count');
            $table->dropColumn('average_ratings');
        });
    }
}