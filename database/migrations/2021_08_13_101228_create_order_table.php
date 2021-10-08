<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->integer('restaurant_id')->nullable();
            $table->string('restaurant_name')->nullable();
            $table->string('order_no')->nullable();
            $table->longText('address')->nullable();
            $table->string('postal')->nullable();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->dateTime('date_time')->nullable();
            $table->longText('remarks')->nullable();
            $table->longText('detail')->nullable();
            $table->decimal('price', 12, 4)->nullable();
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
        Schema::dropIfExists('order');
    }
}
