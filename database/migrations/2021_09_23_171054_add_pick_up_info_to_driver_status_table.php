<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickUpInfoToDriverStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('driver_status', function (Blueprint $table) {
            $table->integer('pick_up_id')->after('date_time')->nullable();
            $table->string('pick_up')->after('pick_up_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_status', function (Blueprint $table) {
            $table->dropColumn(['pick_up_id', 'pick_up']);
        });
    }
}
