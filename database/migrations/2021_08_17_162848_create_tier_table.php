<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTierTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tier', function (Blueprint $table) {
            $table->id();
            $table->integer('restaurant_id')->nullable();
            $table->string('restaurant_name')->nullable();
            $table->string('tier')->nullable();
            $table->decimal('tier_price')->nullable();
            $table->integer('active')->nullable();
            $table->timestamps();
        });

        Schema::table('restaurant', function (Blueprint $table) {
            $table->dropColumn(['tier']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tier');

        Schema::table('restaurant', function (Blueprint $table) {
            $table->string('tier')->after('name')->nullable();
        });
    }
}
