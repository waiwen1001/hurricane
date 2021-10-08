<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurant', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->longText('address')->nullable();
            $table->string('postal')->nullable();
            $table->string('email')->nullable();
            $table->decimal('rebate', 12, 4)->nullable();
            $table->string('tier')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('restaurant_id')->after('user_type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurant');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['restaurant_id']);
        });
    }
}
