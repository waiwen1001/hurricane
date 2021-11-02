<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testing', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_in')->nullable();
            $table->integer('q1')->nullable();
            $table->integer('q1_tips')->nullable();
            $table->integer('q2')->nullable();
            $table->integer('q2_tips')->nullable();
            $table->integer('q3')->nullable();
            $table->integer('q3_tips')->nullable();
            $table->integer('q4')->nullable();
            $table->integer('q4_tips')->nullable();
            $table->integer('point')->nullable();
            $table->integer('hdl')->nullable();
            $table->integer('kr_food')->nullable();
            $table->integer('b_tea')->nullable();
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
        Schema::dropIfExists('testing');
    }
}
