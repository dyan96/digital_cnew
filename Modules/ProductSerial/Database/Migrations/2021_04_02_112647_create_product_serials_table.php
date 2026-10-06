<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductSerialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_serials', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('location_id')->unsigned()->index()->nullable();
            $table->foreign('location_id')->references('id')->on('business_locations');

            $table->integer('product_id')->unsigned()->index()->nullable();
            $table->foreign('product_id')->references('id')->on('products');

            $table->integer('issued_transaction_id')->unsigned()->index()->nullable();
            $table->foreign('issued_transaction_id')->references('id')->on('transactions');
            
            $table->string('serial_no',50);
            $table->tinyInteger('status');
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
        Schema::dropIfExists('product_serials');
    }
}
